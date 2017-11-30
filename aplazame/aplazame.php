<?php
/**
 * This file is part of the official Aplazame module for PrestaShop.
 *
 * @author    Aplazame <soporte@aplazame.com>
 * @copyright 2015-2016 Aplazame
 * @license   see file: LICENSE
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

include_once _PS_MODULE_DIR_ . 'aplazame/lib/Aplazame/Aplazame/autoload.php';
include_once _PS_MODULE_DIR_ . 'aplazame/lib/Aplazame/Sdk/autoload.php';

/**
 * @property bool bootstrap
 * @property string confirmUninstall
 * @property string url
 */
class Aplazame extends PaymentModule
{
    const LOG_INFO = 1;
    const LOG_WARNING = 2;
    const LOG_ERROR = 3;
    const LOG_CRITICAL = 4;
    const ORDER_STATE_PENDING = 'APLAZAME_OS_PENDING';

    /**
     * @var string
     */
    private $apiBaseUri;

    /**
     * @var Aplazame_Sdk_Api_Client
     */
    private $apiClient;

    /**
     * @var string[]
     */
    private $limited_currencies;

    public function __construct()
    {
        $this->name = 'aplazame';
        $this->tab = 'payments_gateways';
        $this->version = '4.0.3';
        $this->author = 'Aplazame';
        $this->author_uri = 'https://aplazame.com';
        $this->module_key = '64b13ea3527b4df3fe2e3fc1526ce515';

        parent::__construct();

        $this->displayName = $this->l('Aplazame: buy now, pay later');
        $this->description = $this->l('Boost sales by 50% with Aplazame, a risk free payment method that offers instant credit for online purchases.');
        $this->confirmUninstall = $this->l('Are you sure about removing these details?');

        $this->need_instance = 0;
        $this->bootstrap = true;

        $this->url = 'https://aplazame.com';

        $this->apiBaseUri = getenv('APLAZAME_API_BASE_URI') ? getenv('APLAZAME_API_BASE_URI') : 'https://api.aplazame.com';
    }

    public function install()
    {
        if (!parent::install()) {
            return false;
        }

        if (!extension_loaded('curl')) {
            $this->_errors[] = $this->l('You have to enable the cURL extension on your server to install this module');

            return false;
        }

        if (!$this->installOrderState()) {
            return false;
        }

        Configuration::updateValue('APLAZAME_SANDBOX', false);
        Configuration::updateValue('APLAZAME_BUTTON_IMAGE', 'white-148x46');
        Configuration::updateValue('APLAZAME_BUTTON', '#aplazame_payment_button');
        Configuration::updateValue('APLAZAME_WIDGET_PROD', '0');

        return ($this->registerHook('actionOrderSlipAdd')
            && $this->registerHook('actionOrderStatusPostUpdate')
            && $this->registerHook('displayAdminProductsExtra')
            && $this->registerHook('displayHeader')
            && $this->registerHook('displayPayment')
            && $this->registerHook('displayProductButtons')
            && $this->registerHook('displayRightColumn')
            && $this->registerHook('displayRightColumnProduct')
            && $this->registerHook('displayShoppingCart')
            && $this->registerHook('payment')
            && $this->registerHook('paymentOptions')
            && $this->registerHook('paymentReturn')
            && $this->registerController('AdminAplazameApiProxy', 'Aplazame API Proxy')
        );
    }

    public function installOrderState()
    {
        $orderStatePending = Configuration::get(self::ORDER_STATE_PENDING);
        if ($orderStatePending && Validate::isLoadedObject(new OrderState($orderStatePending))) {
            return true;
        }

        $order_state = new OrderState();
        $order_state->name = array();
        foreach (Language::getLanguages() as $language) {
            $order_state->name[$language['id_lang']] = 'Awaiting for Aplazame payment';
        }
        $order_state->send_email = false;
        $order_state->color = '#4169E1';
        $order_state->hidden = false;
        $order_state->delivery = false;
        $order_state->logable = false;
        $order_state->invoice = false;
        if ($order_state->add()) {
            Configuration::updateValue(self::ORDER_STATE_PENDING, (int) $order_state->id);
        }

        return true;
    }

    public function accept(
        Cart $cart,
        $fraud
    ) {
        if ($fraud) {
            $this->deny($cart, $fraud);

            return false;
        }

        $cartId = $cart->id;
        $orderStateId = Configuration::get('PS_OS_PAYMENT');

        if (!$cart->orderExists()) {
            return !(false === $this->validateOrder(
                $cartId,
                $orderStateId,
                $cart->getOrderTotal(true),
                $this->displayName
            ));
        }

        return $this->setOrderStateToOrderByCartId($cartId, $orderStateId);
    }

    public function deny(
        Cart $cart,
        $fraud
    ) {
        $cartId = $cart->id;
        $orderStateId = (int) Configuration::get('PS_OS_CANCELED');

        if (!$cart->orderExists()) {
            return $this->validateOrder(
                $cartId,
                $orderStateId,
                $cart->getOrderTotal(true),
                $this->displayName
            );
        }

        return $this->setOrderStateToOrderByCartId($cartId, $orderStateId);
    }

    public function uninstall()
    {
        Configuration::deleteByName('APLAZAME_SANDBOX');

        return parent::uninstall();
    }

    /**
     * Load the configuration form.
     */
    public function getContent()
    {
        $output = '';

        $settings = array();
        $settingsKeys = array(
            'APLAZAME_SANDBOX',
            'APLAZAME_BUTTON',
            'APLAZAME_SECRET_KEY',
            'APLAZAME_BUTTON_IMAGE',
            'APLAZAME_WIDGET_PROD',
        );

        if (Tools::isSubmit('submitAplazameModule')) {
            $hasFoundErrors = false;

            foreach ($settingsKeys as $key) {
                $value = Tools::getValue($key);

                switch ($key) {
                    case 'APLAZAME_SECRET_KEY':
                        try {
                            $this->updateSettingsFromAplazame($value);

                            Configuration::updateValue($key, $value);
                        } catch (Aplazame_Sdk_Api_ApiClientException $apiClientException) {
                            $output .= $this->displayError($apiClientException->getMessage());
                            $hasFoundErrors = true;
                        }

                        break;
                    default:
                        Configuration::updateValue($key, $value);
                }

                $settings[$key] = $value;
            }

            if (!$hasFoundErrors) {
                $output .= $this->displayConfirmation($this->l('Settings updated'));
            }
        } else {
            foreach ($settingsKeys as $key) {
                $settings[$key] = Configuration::get($key);
            }
        }

        if (_PS_VERSION_ < 1.6) {
            $output .= <<<HTML
<style>
    label[for="active_on"],label[for="active_off"]{
        float: none
    }
</style>
HTML;
        }

        return $output . $this->renderForm($settings);
    }

    public function updateSettingsFromAplazame($privateKey)
    {
        $client = new Aplazame_Sdk_Api_Client(
            getenv('APLAZAME_API_BASE_URI') ? getenv('APLAZAME_API_BASE_URI') : 'https://api.aplazame.com',
            (Configuration::get('APLAZAME_SANDBOX') ? Aplazame_Sdk_Api_Client::ENVIRONMENT_SANDBOX : Aplazame_Sdk_Api_Client::ENVIRONMENT_PRODUCTION),
            $privateKey
        );

        $link = Context::getContext()->link;

        $response = $client->patch('/me', array(
            'confirmation_url' => $link->getModuleLink('aplazame', 'api', array('path' => '/confirm/'))
        ));

        Configuration::updateValue('APLAZAME_PUBLIC_KEY', $response['public_api_key']);

        return $response;
    }
    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm(array $settings)
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitAplazameModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
                . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $settings,
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {
        return array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Settings'),
                    'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => (_PS_VERSION_ >= 1.6) ? 'switch' : 'radio',
                        'label' => $this->l('Test Mode (Sandbox)'),
                        'name' => 'APLAZAME_SANDBOX',
                        'is_bool' => true,
                        'desc' => $this->l('Determines if the module is on Sandbox mode'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Yes'),
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('No'),
                            ),
                        ),
                    ),
                    array(
                        'name' => 'APLAZAME_SECRET_KEY',
                        'type' => 'text',
                        'label' => $this->l('Private API Key'),
                        'desc' => $this->l('Aplazame API Private Key'),
                        'prefix' => '<i class="icon icon-key"></i>',
                        'col' => 4,
                    ),
                    array(
                        'col' => 4,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-code"></i>',
                        'desc' => $this->l('Aplazame Button CSS Selector'),
                        'name' => 'APLAZAME_BUTTON',
                        'label' => $this->l('Button'),
                    ),
                    array(
                        'col' => 4,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-code"></i>',
                        'desc' => $this->l('Aplazame Button Image that you want to show'),
                        'name' => 'APLAZAME_BUTTON_IMAGE',
                        'label' => $this->l('Button Image'),
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Hook Product Widget'),
                        'desc' => $this->l('Select the hook where you want to display the product widget'),
                        'name' => 'APLAZAME_WIDGET_PROD',
                        'options' => array(
                            'query' => array(
                                array(
                                    'id_option' => 0,
                                    'name' => $this->l('displayProductButtons'),
                                ),
                                array(
                                    'id_option' => 1,
                                    'name' => $this->l('displayRightColumnProduct'),
                                ),
                                array(
                                    'id_option' => 2,
                                    'name' => $this->l('displayRightColumn'),
                                ),
                            ),
                            'id' => 'id_option',
                            'name' => 'name',
                        ),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    public function isAvailable()
    {
        if (!$this->active) {
            return false;
        }

        $privateKey = Configuration::get('APLAZAME_SECRET_KEY');
        $publicKey = Configuration::get('APLAZAME_PUBLIC_KEY');

        return (!empty($privateKey) && !empty($publicKey));
    }

    public function hookActionOrderSlipAdd($params)
    {
        if (!$this->isAvailable()
            || Tools::isSubmit('generateDiscount')
        ) {
            return false;
        }

        /** @var Order $order */
        $order = $params['order'];

        $orderSlips = $order->getOrderSlipsCollection();

        /** @var OrderSlip $lastOrderSlip */
        $lastOrderSlip = $orderSlips->orderBy('date_add', 'desc')->getFirst();

        return $this->refundAmount($order, $lastOrderSlip->total_products_tax_incl + $lastOrderSlip->total_shipping_tax_incl);
    }

    public function hookActionOrderStatusPostUpdate($params)
    {
        $statusObject = $params['newOrderStatus'];
        switch ($statusObject->id) {
            case Configuration::get('PS_OS_CANCELED'):
                $order = new Order($params['id_order']);

                return $this->cancelOrder($order->id_cart);
            default:
                return true;
        }
    }

    public function hookDisplayAdminProductsExtra()
    {
        $id_product = Tools::getValue('id_product');

        $articles = array(Aplazame_Aplazame_Api_BusinessModel_Article::createFromProduct(new Product($id_product, false, $this->context->language->id)));

        $this->context->smarty->assign(array(
            'articles' => $articles,
        ));

        return $this->display(__FILE__, 'views/templates/admin/product.tpl');
    }

    public function hookDisplayHeader()
    {
        if (!$this->isAvailable()) {
            return false;
        }

        $aplazameJsUri = getenv('APLAZAME_JS_URI') ? getenv('APLAZAME_JS_URI') : 'https://aplazame.com/static/aplazame.js';

        $this->context->smarty->assign(array(
            'aplazame_js_uri' => $aplazameJsUri,
            'aplazame_api_base_uri' => $this->apiBaseUri,
            'aplazame_public_key' => Configuration::get('APLAZAME_PUBLIC_KEY'),
            'aplazame_is_sandbox' => Configuration::get('APLAZAME_SANDBOX'),
        ));

        return $this->display(__FILE__, 'header.tpl');
    }

    public function hookDisplayPayment($params)
    {
        return $this->hookPayment($params);
    }

    public function hookDisplayProductButtons($params)
    {
        $displayWidget = (int) Configuration::get('APLAZAME_WIDGET_PROD');
        // display if configured or by default
        if ($displayWidget === 0 || empty($displayWidget)) {
            return $this->getWidget($params);
        }

        return false;
    }

    public function hookDisplayRightColumnProduct($params)
    {
        $displayWidget = (int) Configuration::get('APLAZAME_WIDGET_PROD');
        if ($displayWidget === 1) {
            return $this->getWidget($params);
        }

        return false;
    }

    public function hookDisplayRightColumn($params)
    {
        if (isset($this->context->controller->php_self) && $this->context->controller->php_self == 'product') {
            $displayWidget = (int) Configuration::get('APLAZAME_WIDGET_PROD');
            if ($displayWidget === 2) {
                return $this->getWidget($params);
            }
        }

        return false;
    }

    public function hookDisplayShoppingCart($params)
    {
        /** @var Cart $cart */
        $cart = $params['cart'];

        $currency = new Currency($cart->id_currency);

        $this->context->smarty->assign(array(
            'aplazame_cart_total' => Aplazame_Sdk_Serializer_Decimal::fromFloat($cart->getOrderTotal())->value,
            'aplazame_currency_iso' => $currency->iso_code,
        ));

        return $this->display(__FILE__, 'shoppingcart.tpl');
    }

    /**
     * This method is used to render the payment button,
     * Take care if the button should be displayed or not.
     */
    public function hookPayment($params)
    {
        if (!$this->isAvailable()) {
            return false;
        }

        /** @var Cart $cart */
        $cart = $params['cart'];
        $button_image_uri = 'https://aplazame.com/static/img/buttons/' . Configuration::get('APLAZAME_BUTTON_IMAGE') . '.png';

        $this->context->smarty->assign($this->getButtonTemplateVars($cart));
        $this->context->smarty->assign(array('aplazame_button_image_uri' => $button_image_uri));

        return $this->display(__FILE__, 'payment_1.5.tpl');
    }

    public function hookPaymentOptions($params)
    {
        if (!$this->isAvailable()) {
            return array();
        }

        /** @var Cart $cart */
        $cart = $params['cart'];

        $link = $this->context->link;
        $this->context->smarty->assign($this->getButtonTemplateVars($cart));

        $newOption = new PrestaShop\PrestaShop\Core\Payment\PaymentOption();
        $newOption->setCallToActionText($this->l('Pay with Aplazame'))
            ->setAction($link->getModuleLink('aplazame', 'redirect'))
            ->setAdditionalInformation($this->fetch('module:aplazame/views/templates/hook/payment_1.7.tpl'))
        ;

        return array(
            $newOption,
        );
    }

    public function hookPaymentReturn($params)
    {
        /** @var Order $order */
        if (isset($params['order'])) {
            $order = $params['order'];
        } else {
            $order = $params['objOrder'];
        }

        $this->context->smarty->assign(array(
            'shop_name' => $this->context->shop->name,
            'reference' => $order->reference,
        ));

        $currentState = $order->getCurrentState();
        $successfullyStates = array(
            Configuration::get('PS_OS_PAYMENT'),
            Configuration::get('PS_OS_OUTOFSTOCK'),
        );

        if (in_array($currentState, $successfullyStates)) {
            return $this->display(__FILE__, 'confirmation_success.tpl');
        }

        return $this->display(__FILE__, 'confirmation_failure.tpl');
    }

    public function getWidget($params)
    {
        if (!$this->isAvailable()) {
            return false;
        }

        if (isset($params['product']) && $params['product'] instanceof Product) {
            $product = $params['product'];
        } elseif (Tools::getValue('controller') === 'product' && Tools::getValue('id_product')) {
            $product = new Product(Tools::getValue('id_product'));
        } elseif (method_exists($this->context->controller, 'getProduct')) {
            $product = $this->context->controller->getProduct();
        }

        if (!isset($product) || !Validate::isLoadedObject($product)) {
            return false;
        }

        if (!$product->show_price || !$product->available_for_order) {
            return false;
        }

        $currency = Context::getContext()->currency;
        $this->context->smarty->assign(array(
            'aplazame_amount' => Aplazame_Sdk_Serializer_Decimal::fromFloat($product->getPrice(true, null, 2))->value,
            'aplazame_currency_iso' => $currency->iso_code,
        ));

        return $this->display(__FILE__, 'product.tpl');
    }

    public function getApiClient()
    {
        if (!$this->apiClient) {
            $this->apiClient = new Aplazame_Sdk_Api_Client(
                $this->apiBaseUri,
                Configuration::get('APLAZAME_SANDBOX') ? Aplazame_Sdk_Api_Client::ENVIRONMENT_SANDBOX : Aplazame_Sdk_Api_Client::ENVIRONMENT_PRODUCTION,
                Configuration::get('APLAZAME_SECRET_KEY')
            );
        }

        return $this->apiClient;
    }

    public function callToRest($method, $path, array $values = null)
    {
        $client = $this->getApiClient();

        try {
            return $client->request($method, $path, $values);
        } catch (Exception $e) {
            $this->log(self::LOG_ERROR, $e->getMessage());

            throw $e;
        }
    }

    public function log($severity, $message, $mid = null)
    {
        if (!class_exists('PrestaShopLogger')) {
            return;
        }

        $objectType = $mid ? 'Cart' : null;
        $objectId = $mid;

        PrestaShopLogger::addLog($message, $severity, null, $objectType, $objectId);
    }

    public function refundAmount(Order $order, $amount)
    {
        $mid = $order->id_cart;

        $decimal = Aplazame_Sdk_Serializer_Decimal::fromFloat($amount)->value;
        try {
            $this->callToRest('POST', '/orders/' . $mid . '/refund', array('amount' => $decimal));
        } catch (Exception $e) {
            $this->log(self::LOG_CRITICAL, 'Cannot refund. Detail ' . $e->getMessage(), $mid);

            return false;
        }

        return $order->addOrderPayment(-$amount, $this->displayName);
    }

    public function checkCurrency(Currency $currency)
    {
        if (!in_array($currency->iso_code, $this->limited_currencies)) {
            return false;
        }

        return true;
    }

    /**
     * @param string $mid
     *
     * @return bool
     */
    public function cancelOrder($mid)
    {
        try {
            $this->callToRest('POST', '/orders/' . $mid . '/cancel');
        } catch (Exception $e) {
            $this->log(self::LOG_CRITICAL, 'Cannot cancel. Detail ' . $e->getMessage(), $mid);

            return false;
        }

        return true;
    }

    private function registerController($className, $name)
    {
        if (Tab::getIdFromClassName($className)) {
            return true;
        }

        $tab = new Tab();
        $tab->active = 1;
        $tab->name = array();
        $tab->class_name = $className;
        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = $name;
        }
        $tab->id_parent = -1;
        $tab->module = 'aplazame';

        return (boolean) $tab->add();
    }

    private function getButtonTemplateVars(Cart $cart)
    {
        $currency = new Currency((int) ($cart->id_currency));

        return array(
            'aplazame_button' => Configuration::get('APLAZAME_BUTTON'),
            'aplazame_currency_iso' => $currency->iso_code,
            'aplazame_cart_total' => Aplazame_Sdk_Serializer_Decimal::fromFloat($cart->getOrderTotal())->value,
        );
    }

    private function setOrderStateToOrderByCartId($cartId, $orderStateId)
    {
        $orderId = Order::getOrderByCartId($cartId);
        $order = new Order($orderId);
        if (!Validate::isLoadedObject($order)) {
            return false;
        }

        $order->setCurrentState($orderStateId);

        return true;
    }
}

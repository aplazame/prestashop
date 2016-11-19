<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

include_once _PS_MODULE_DIR_ . 'aplazame/api/Serializers.php';
include_once _PS_MODULE_DIR_ . 'aplazame/api/Client.php';

/**
 * @property bool bootstrap
 * @property string confirmUninstall
 * @property string url
 */
class Aplazame extends PaymentModule
{
    const VERSION = '3.0.1';
    const LOG_INFO = 1;
    const LOG_WARNING = 2;
    const LOG_ERROR = 3;
    const LOG_CRITICAL = 4;

    /**
     * @var string
     */
    private $apiBaseUri;

    /**
     * @var AplazameClient
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
        $this->version = '3.0.1';
        $this->author = 'Aplazame';
        $this->author_uri = 'https://aplazame.com';
        $this->limited_countries = array('ES');

        parent::__construct();

        $this->displayName = $this->l('Aplazame: buy now, pay later');
        $this->description = $this->l('Boost sales by 50% with Aplazame, a risk free payment method that offers instant credit for online purchases.');
        $this->confirmUninstall = $this->l('Are you sure about removing these details?');

        $this->need_instance = 0;
        $this->bootstrap = true;

        $this->url = 'https://aplazame.com';

        $this->limited_currencies = array('EUR');
        $this->apiBaseUri = getenv('APLAZAME_API_BASE_URI') ? getenv('APLAZAME_API_BASE_URI') : 'https://api.aplazame.com';
    }

    public function install()
    {
        if (!extension_loaded('curl')) {
            $this->_errors[] = $this->l('You have to enable the cURL extension on your server to install this module');

            return false;
        }

        $iso_code = Country::getIsoById(Configuration::get('PS_COUNTRY_DEFAULT'));
        if (!in_array($iso_code, $this->limited_countries)) {
            $this->_errors[] = $this->l('This module is not available in your country');

            return false;
        }

        Configuration::updateValue('APLAZAME_SANDBOX', false);
        Configuration::updateValue('APLAZAME_BUTTON_IMAGE', 'white-148x46');
        Configuration::updateValue('APLAZAME_BUTTON', '#aplazame_payment_button');
        Configuration::updateValue('APLAZAME_WIDGET_PROD', '0');

        return (parent::install()
            && $this->registerHook('actionOrderSlipAdd')
            && $this->registerHook('actionOrderStatusPostUpdate')
            && $this->registerHook('displayAdminProductsExtra')
            && $this->registerHook('displayHeader')
            && $this->registerHook('displayPayment')
            && $this->registerHook('displayProductButtons')
            && $this->registerHook('displayRightColumn')
            && $this->registerHook('displayRightColumnProduct')
            && $this->registerHook('displayShoppingCart')
            && $this->registerHook('payment')
            && $this->registerHook('paymentReturn')
            && $this->registerController('AdminAplazameApiProxy', 'Aplazame API Proxy')
        );
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
            'APLAZAME_PUBLIC_KEY',
            'APLAZAME_BUTTON_IMAGE',
            'APLAZAME_WIDGET_PROD',
        );

        if (Tools::isSubmit('submitAplazameModule')) {
            foreach ($settingsKeys as $key) {
                $value = Tools::getValue($key);

                Configuration::updateValue($key, $value);
                $settings[$key] = $value;
            }

            $output .= $this->displayConfirmation($this->l('Settings updated'));
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
                        'label' => $this->l('Sandbox'),
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
                        'name' => 'APLAZAME_PUBLIC_KEY',
                        'type' => 'text',
                        'label' => $this->l('Public API Key'),
                        'desc' => $this->l('Aplazame API Public Key'),
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

        $articles = array(AplazameSerializers::getArticleCampaign(new Product($id_product, false, $this->context->language->id)));

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
        $this->context->smarty->assign(array(
            'aplazame_amount' => AplazameSerializers::formatDecimals($params['total_price']),
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

        $currency_id = $cart->id_currency;
        $currency = new Currency((int) $currency_id);

        if (!in_array($currency->iso_code, $this->limited_currencies)) {
            return false;
        }

        $button_image_uri = 'https://aplazame.com/static/img/buttons/' . Configuration::get('APLAZAME_BUTTON_IMAGE') . '.png';

        $this->context->smarty->assign(array(
            'aplazame_button' => Configuration::get('APLAZAME_BUTTON'),
            'aplazame_currency_iso' => $currency->iso_code,
            'aplazame_cart_total' => AplazameSerializers::formatDecimals($cart->getOrderTotal()),
            'aplazame_button_image_uri' => $button_image_uri,
        ));

        return $this->display(__FILE__, 'payment.tpl');
    }

    public function hookPaymentReturn($params)
    {
        /** @var Order $order */
        $order = $params['objOrder'];

        $this->context->smarty->assign(array(
            'reference' => $order->reference,
        ));

        if ($order->getCurrentState() === Configuration::get('PS_OS_PAYMENT')) {
            return $this->display(__FILE__, 'confirmation_success.tpl');
        }

        return $this->display(__FILE__, 'confirmation_failure.tpl');
    }

    public function getWidget($params)
    {
        if (!$this->isAvailable()) {
            return false;
        }

        if (isset($params['product'])) {
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

        $this->context->smarty->assign(array(
            'aplazame_amount' => AplazameSerializers::formatDecimals($product->getPrice(true, null, 2)),
        ));

        return $this->display(__FILE__, 'product.tpl');
    }

    public function getApiClient()
    {
        if (!$this->apiClient) {
            $this->apiClient = new AplazameClient(
                $this->apiBaseUri,
                Configuration::get('APLAZAME_SECRET_KEY'),
                Configuration::get('APLAZAME_SANDBOX'),
                $this->version
            );
        }

        return $this->apiClient;
    }

    public function callToRest($method, $path, array $values = null)
    {
        $client = $this->getApiClient();

        try {
            return $client->callToRest($method, $path, $values);
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

        $decimal = AplazameSerializers::formatDecimals($amount);
        $response = $this->callToRest('POST', '/orders/' . $mid . '/refund', array('amount' => $decimal));
        if ($response['is_error']) {
            $this->log(self::LOG_CRITICAL, 'Cannot refund. Detail ' . $response['payload']['error']['message'], $mid);

            return false;
        }

        return $order->addOrderPayment(-$amount, $this->displayName);
    }

    /**
     * @param string $mid
     *
     * @return bool
     */
    public function cancelOrder($mid)
    {
        $response = $this->callToRest('POST', '/orders/' . $mid . '/cancel');
        if ($response['is_error']) {
            $this->log(self::LOG_CRITICAL, 'Cannot cancel. Detail ' . $response['payload']['error']['message'], $mid);

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
}

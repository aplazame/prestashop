<?php
/**
 * This file is part of the official Aplazame module for PrestaShop.
 *
 * @author    Aplazame <soporte@aplazame.com>
 * @copyright 2015-2023 Aplazame
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
        $this->version = '7.9.2';
        $this->author = 'Aplazame SL';
        $this->author_uri = 'https://aplazame.com';
        $this->module_key = '64b13ea3527b4df3fe2e3fc1526ce515';

        parent::__construct();

        $this->displayName = $this->l('Aplazame');
        $this->description = $this->l('Boost sales by 50% with Aplazame, a risk free payment method that offers instant credit for online purchases.');
        $this->confirmUninstall = $this->l('Are you sure about removing these details?');

        $this->need_instance = 0;
        $this->bootstrap = true;

        $this->url = 'https://aplazame.com';

        $this->apiBaseUri = getenv('APLAZAME_API_BASE_URI') ? getenv('APLAZAME_API_BASE_URI') : 'https://api.aplazame.com';
        if (!Configuration::get('APLAZAME_SECRET_KEY')) {
            $this->warning = $this->l('Aplazame API key must be configured before using this module.');
        }
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

        /**
         * Checkout settings.
         */
        Configuration::updateValue('APLAZAME_SANDBOX', false);
        Configuration::updateValue('APLAZAME_CREATE_ORDER_AT_CHECKOUT', true);
        Configuration::updateValue('APLAZAME_BUTTON_TITLE', $this->l('Pay with Aplazame'));
        Configuration::updateValue('APLAZAME_BUTTON_DESCRIPTION',
            'Compra primero y paga después con <a href="https://aplazame.com" target="_blank">Aplazame</a>.', true);

        if (_PS_VERSION_ >= 1.7) {
            Configuration::updateValue('APLAZAME_BUTTON_IMAGE', '');
            Configuration::updateValue('APLAZAME_BUTTON', "div.payment-option:has(input[data-module-name='{$this->name}'])");
        } else {
            Configuration::updateValue('APLAZAME_BUTTON_IMAGE', 'https://cdn.aplazame.com/static/img/buttons/aplazame-blended-button-227px.png');
            Configuration::updateValue('APLAZAME_BUTTON', '#aplazame_payment_button');
        }

        /**
         * Widgets settings.
         */
        Configuration::updateValue('APLAZAME_WIDGET_OUT_OF_LIMITS', 'show');
        Configuration::updateValue('APLAZAME_WIDGET_PROD', '0');
        Configuration::updateValue('APLAZAME_PRODUCT_WIDGET_ENABLED', true);
        Configuration::updateValue('APLAZAME_PRODUCT_LEGAL_ADVICE', true);
        Configuration::updateValue('APLAZAME_PRODUCT_DOWNPAYMENT_INFO', true);
        Configuration::updateValue('APLAZAME_PRODUCT_PAY_IN_4', false);
        Configuration::updateValue('APLAZAME_PRODUCT_DEFAULT_INSTALMENTS', '');
        Configuration::updateValue('APLAZAME_PRODUCT_CSS', '');
        Configuration::updateValue('APLAZAME_CART_WIDGET_ENABLED', true);
        Configuration::updateValue('APLAZAME_CART_LEGAL_ADVICE', true);
        Configuration::updateValue('APLAZAME_CART_DOWNPAYMENT_INFO', true);
        Configuration::updateValue('APLAZAME_CART_PAY_IN_4', false);
        Configuration::updateValue('APLAZAME_CART_DEFAULT_INSTALMENTS', '');
        Configuration::updateValue('APLAZAME_CART_CSS', '#total_price');

        /**
         * Widget v4 settings.
         */
        Configuration::updateValue('WIDGET_LEGACY', false);
        Configuration::updateValue('PRODUCT_WIDGET_BORDER', true);
        Configuration::updateValue('PRODUCT_WIDGET_PRIMARY_COLOR', '#334bff');
        Configuration::updateValue('PRODUCT_WIDGET_LAYOUT', 'horizontal');
        Configuration::updateValue('PRODUCT_WIDGET_ALIGN', 'center');
        Configuration::updateValue('PRODUCT_WIDGET_MAX_DESIRED', false);
        Configuration::updateValue('CART_WIDGET_PRIMARY_COLOR', '#334bff');
        Configuration::updateValue('CART_WIDGET_LAYOUT', 'horizontal');
        Configuration::updateValue('CART_WIDGET_ALIGN', 'center');
        Configuration::updateValue('CART_WIDGET_MAX_DESIRED', false);

        /**
         * Developer settings.
         */
        Configuration::updateValue('APLAZAME_V4', false);

        return ($this->registerHook('actionOrderSlipAdd')
            && $this->registerHook('actionOrderStatusUpdate')
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
        $order_state->unremovable = true;
        if ($order_state->add()) {
            Configuration::updateValue(self::ORDER_STATE_PENDING, (int) $order_state->id);
        }

        return true;
    }

    public function pending(Cart $cart)
    {
        if (!$cart->orderExists()) {
            $cartId = $cart->id;
            $orderStateId = Configuration::get(self::ORDER_STATE_PENDING);

            $customer = new Customer($cart->id_customer);

            return !(false === $this->validateOrder(
                $cartId,
                $orderStateId,
                $cart->getOrderTotal(true),
                $this->displayName,
                'Waiting for Aplazame payment/review',
                array(),
                null,
                false,
                $customer->secure_key
            ));
        }

        return true;
    }

    public function accept(Cart $cart)
    {
        $cartId = $cart->id;
        $orderStateId = Configuration::get('PS_OS_PAYMENT');

        $customer = new Customer($cart->id_customer);

        if (!$cart->orderExists()) {
            return !(false === $this->validateOrder(
                $cartId,
                $orderStateId,
                $cart->getOrderTotal(true),
                $this->displayName,
                null,
                array(),
                null,
                false,
                $customer->secure_key
            ));
        }

        return $this->setOrderStateToOrderByCartId($cartId, $orderStateId);
    }

    public function deny(Cart $cart)
    {
        if (!$cart->orderExists()) {
            return true;
        }

        $cartId = $cart->id;
        $orderStateId = (int) Configuration::get('PS_OS_CANCELED');

        return $this->setOrderStateToOrderByCartId($cartId, $orderStateId);
    }

    public function uninstall()
    {
        $order_state = new OrderState(Configuration::get(self::ORDER_STATE_PENDING));
        $order_state->delete();

        Configuration::deleteByName(self::ORDER_STATE_PENDING);
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
            'APLAZAME_SECRET_KEY',
            'APLAZAME_CREATE_ORDER_AT_CHECKOUT',
            'APLAZAME_PRODUCT_WIDGET_ENABLED',
            'APLAZAME_WIDGET_PROD',
            'APLAZAME_CART_WIDGET_ENABLED',
            'APLAZAME_BUTTON',
            'APLAZAME_BUTTON_IMAGE',
            'APLAZAME_BUTTON_TITLE',
            'APLAZAME_BUTTON_DESCRIPTION',
            'APLAZAME_PRODUCT_LEGAL_ADVICE',
            'APLAZAME_CART_LEGAL_ADVICE',
            'APLAZAME_PRODUCT_DOWNPAYMENT_INFO',
            'APLAZAME_CART_DOWNPAYMENT_INFO',
            'APLAZAME_PRODUCT_PAY_IN_4',
            'APLAZAME_CART_PAY_IN_4',
            'APLAZAME_PRODUCT_DEFAULT_INSTALMENTS',
            'APLAZAME_CART_DEFAULT_INSTALMENTS',
            'APLAZAME_PRODUCT_CSS',
            'APLAZAME_CART_CSS',
            'APLAZAME_WIDGET_OUT_OF_LIMITS',
            'APLAZAME_V4',
            'WIDGET_LEGACY',
            'PRODUCT_WIDGET_BORDER',
            'PRODUCT_WIDGET_PRIMARY_COLOR',
            'PRODUCT_WIDGET_LAYOUT',
            'PRODUCT_WIDGET_ALIGN',
            'PRODUCT_WIDGET_MAX_DESIRED',
            'CART_WIDGET_PRIMARY_COLOR',
            'CART_WIDGET_LAYOUT',
            'CART_WIDGET_ALIGN',
            'CART_WIDGET_MAX_DESIRED',
        );

        if (Tools::isSubmit('submitAplazameModule')) {
            $hasFoundErrors = false;

            foreach ($settingsKeys as $key) {
                $value = Tools::getValue($key);

                switch ($key) {
                    case 'APLAZAME_BUTTON_DESCRIPTION':
                        Configuration::updateValue($key, $value, true);
                        break;
                    case 'APLAZAME_SECRET_KEY':
                        if ($value != Configuration::get('APLAZAME_SECRET_KEY')) {
                            try {
                                $this->updateSettingsFromAplazame($value);

                                Configuration::updateValue($key, $value);
                            } catch (Aplazame_Sdk_Api_ApiClientException $apiClientException) {
                                $output .= $this->displayError($apiClientException->getMessage());
                                $hasFoundErrors = true;
                            }
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

        $response = $client->get('/merchants/api-keys');

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

        return $helper->generateForm($this->getConfigForm());
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {
        /**
         * PS 1.5 compatibility radio buttons.
         */
        $switch_or_radio = (_PS_VERSION_ >= 1.6) ? 'switch' : 'radio';

        /**
         * Settings form.
         */
        $settings =
            array(
                'form' => array(
                    'legend' => array(
                        'title' => $this->l('Settings'),
                        'icon' => 'icon-cogs',
                    ),
                    'input' => array(
                        array(
                            'type' => $switch_or_radio,
                            'label' => $this->l('Test Mode (Sandbox)'),
                            'name' => 'APLAZAME_SANDBOX',
                            'is_bool' => true,
                            'desc' => $this->l('Determines if the module is on Sandbox mode'),
                            'values' => array(
                                array(
                                    'id' => 'active_on',
                                    'value' => true,
                                ),
                                array(
                                    'id' => 'active_off',
                                    'value' => false,
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
                            'type' => $switch_or_radio,
                            'label' => $this->l('Create order at checkout'),
                            'name' => 'APLAZAME_CREATE_ORDER_AT_CHECKOUT',
                            'is_bool' => true,
                            'desc' => $this->l('Create order at checkout (with awaiting state) instead at confirmation'),
                            'values' => array(
                                array(
                                    'id' => 'active_on',
                                    'value' => true,
                                ),
                                array(
                                    'id' => 'active_off',
                                    'value' => false,
                                ),
                            ),
                        ),
                        array(
                            'name' => 'APLAZAME_WIDGET_OUT_OF_LIMITS',
                            'type' => 'select',
                            'label' => $this->l('Widget if Aplazame is not available'),
                            'desc' => $this->l('Show/hide alternative widget if Aplazame is not available'),
                            'options' => array(
                                'query' => array(
                                    array(
                                        'id_option' => 'show',
                                        'name' => $this->l('Show'),
                                    ),
                                    array(
                                        'id_option' => 'hide',
                                        'name' => $this->l('Hide'),
                                    ),
                                ),
                                'id' => 'id_option',
                                'name' => 'name',
                            ),
                        ),
                        array(
                            'name' => 'WIDGET_LEGACY',
                            'type' => $switch_or_radio,
                            'label' => $this->l('Turn on widget legacy'),
                            'desc' => $this->l('Use widget legacy instead new widget'),
                            'values' => array(
                                array(
                                    'id' => 'active_on',
                                    'value' => true,
                                ),
                                array(
                                    'id' => 'active_off',
                                    'value' => false,
                                ),
                            ),
                        ),
                    ),
                ),
            );

        /**
         * Widgets forms.
         */
        $product_widget =
            array(
                'form' => array(
                    'legend' => array(
                        'title' => $this->l('Product widget'),
                        'icon' => 'icon-cogs',
                    ),
                    'input' => array(
                        array(
                            'type' => $switch_or_radio,
                            'label' => $this->l('Show widget on product page'),
                            'name' => 'APLAZAME_PRODUCT_WIDGET_ENABLED',
                            'is_bool' => true,
                            'values' => array(
                                array(
                                    'id' => 'active_on',
                                    'value' => true,
                                ),
                                array(
                                    'id' => 'active_off',
                                    'value' => false,
                                ),
                            ),
                        ),
                        array(
                            'type' => $switch_or_radio,
                            'label' => $this->l('Downpayment info'),
                            'name' => 'APLAZAME_PRODUCT_DOWNPAYMENT_INFO',
                            'is_bool' => true,
                            'desc' => $this->l('Show downpayment info in product widget'),
                            'values' => array(
                                array(
                                    'id' => 'active_on',
                                    'value' => true,
                                ),
                                array(
                                    'id' => 'active_off',
                                    'value' => false,
                                ),
                            ),
                        ),
                        array(
                            'type' => $switch_or_radio,
                            'label' => $this->l('Legal notice'),
                            'name' => 'APLAZAME_PRODUCT_LEGAL_ADVICE',
                            'is_bool' => true,
                            'desc' => $this->l('Show legal notice in product widget'),
                            'values' => array(
                                array(
                                    'id' => 'active_on',
                                    'value' => true,
                                ),
                                array(
                                    'id' => 'active_off',
                                    'value' => false,
                                ),
                            ),
                        ),
                        array(
                            'type' => $switch_or_radio,
                            'label' => $this->l('Pay in 4'),
                            'name' => 'APLAZAME_PRODUCT_PAY_IN_4',
                            'is_bool' => true,
                            'desc' => $this->l('Enable product widget pay in 4 (if available)'),
                            'values' => array(
                                array(
                                    'id' => 'active_on',
                                    'value' => true,
                                ),
                                array(
                                    'id' => 'active_off',
                                    'value' => false,
                                ),
                            ),
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
                        array(
                            'type' => 'html',
                            'label' => $this->l('Default instalments'),
                            'desc' => $this->l('Select the default number instalments for the product widget'),
                            'name' => 'APLAZAME_PRODUCT_DEFAULT_INSTALMENTS',
                            'html_content' => '<input type="number" min="1" name="APLAZAME_PRODUCT_DEFAULT_INSTALMENTS" value="' . Configuration::get('APLAZAME_PRODUCT_DEFAULT_INSTALMENTS') . '">',
                        ),
                        array(
                            'col' => 4,
                            'type' => 'text',
                            'prefix' => '<i class="icon icon-code"></i>',
                            'desc' => $this->l('CSS selector pointing to variable product price'),
                            'name' => 'APLAZAME_PRODUCT_CSS',
                            'label' => $this->l('Variable price CSS'),
                        ),
                        array(
                            'type' => $switch_or_radio,
                            'label' => $this->l('Border'),
                            'name' => 'PRODUCT_WIDGET_BORDER',
                            'is_bool' => true,
                            'desc' => $this->l('Show border in product widget (only new widget)'),
                            'values' => array(
                                array(
                                    'id' => 'active_on',
                                    'value' => true,
                                ),
                                array(
                                    'id' => 'active_off',
                                    'value' => false,
                                ),
                            ),
                        ),
                        array(
                            'type' => $switch_or_radio,
                            'label' => $this->l('Enter maximum instalment'),
                            'name' => 'PRODUCT_WIDGET_MAX_DESIRED',
                            'is_bool' => true,
                            'desc' => $this->l('Allow the user to manually enter the maximum instalment they want to pay (only new widget)'),
                            'values' => array(
                                array(
                                    'id' => 'active_on',
                                    'value' => true,
                                ),
                                array(
                                    'id' => 'active_off',
                                    'value' => false,
                                ),
                            ),
                        ),
                        array(
                            'name' => 'PRODUCT_WIDGET_PRIMARY_COLOR',
                            'type' => 'color',
                            'label' => $this->l('Primary color'),
                            'desc' => $this->l('Primary color hexadecimal code for product widget (only new widget)'),
                        ),
                        array(
                            'name' => 'PRODUCT_WIDGET_LAYOUT',
                            'type' => 'select',
                            'label' => $this->l('Layout'),
                            'desc' => $this->l('Layout of product widget (only new widget)'),
                            'options' => array(
                                'query' => array(
                                    array(
                                        'id_option' => 'horizontal',
                                        'name' => $this->l('Horizontal'),
                                    ),
                                    array(
                                        'id_option' => 'vertical',
                                        'name' => $this->l('Vertical'),
                                    ),
                                ),
                                'id' => 'id_option',
                                'name' => 'name',
                            ),
                        ),
                        array(
                            'name' => 'PRODUCT_WIDGET_ALIGN',
                            'type' => 'select',
                            'label' => $this->l('Alignment'),
                            'desc' => $this->l('Product widget alignment (only new widget)'),
                            'options' => array(
                                'query' => array(
                                    array(
                                        'id_option' => 'left',
                                        'name' => $this->l('Left'),
                                    ),
                                    array(
                                        'id_option' => 'center',
                                        'name' => $this->l('Center'),
                                    ),
                                    array(
                                        'id_option' => 'right',
                                        'name' => $this->l('Right'),
                                    ),
                                ),
                                'id' => 'id_option',
                                'name' => 'name',
                            ),
                        ),
                    ),
                ),
            );

        $cart_widget =
            array(
                'form' => array(
                    'legend' => array(
                        'title' => $this->l('Cart widget'),
                        'icon' => 'icon-cogs',
                    ),
                    'input' => array(
                        array(
                            'type' => $switch_or_radio,
                            'label' => $this->l('Show widget on cart page'),
                            'name' => 'APLAZAME_CART_WIDGET_ENABLED',
                            'is_bool' => true,
                            'values' => array(
                                array(
                                    'id' => 'active_on',
                                    'value' => true,
                                ),
                                array(
                                    'id' => 'active_off',
                                    'value' => false,
                                ),
                            ),
                        ),
                        array(
                            'type' => $switch_or_radio,
                            'label' => $this->l('Downpayment info'),
                            'name' => 'APLAZAME_CART_DOWNPAYMENT_INFO',
                            'is_bool' => true,
                            'desc' => $this->l('Show downpayment info in cart widget'),
                            'values' => array(
                                array(
                                    'id' => 'active_on',
                                    'value' => true,
                                ),
                                array(
                                    'id' => 'active_off',
                                    'value' => false,
                                ),
                            ),
                        ),
                        array(
                            'type' => $switch_or_radio,
                            'label' => $this->l('Legal notice'),
                            'name' => 'APLAZAME_CART_LEGAL_ADVICE',
                            'is_bool' => true,
                            'desc' => $this->l('Show legal notice in cart widget'),
                            'values' => array(
                                array(
                                    'id' => 'active_on',
                                    'value' => true,
                                ),
                                array(
                                    'id' => 'active_off',
                                    'value' => false,
                                ),
                            ),
                        ),
                        array(
                            'type' => $switch_or_radio,
                            'label' => $this->l('Pay in 4'),
                            'name' => 'APLAZAME_CART_PAY_IN_4',
                            'is_bool' => true,
                            'desc' => $this->l('Enable cart widget pay in 4 (if available)'),
                            'values' => array(
                                array(
                                    'id' => 'active_on',
                                    'value' => true,
                                ),
                                array(
                                    'id' => 'active_off',
                                    'value' => false,
                                ),
                            ),
                        ),
                        array(
                            'type' => 'html',
                            'label' => $this->l('Default instalments'),
                            'desc' => $this->l('Select the default number instalments for the cart widget'),
                            'name' => 'APLAZAME_CART_DEFAULT_INSTALMENTS',
                            'html_content' => '<input type="number" min="1" name="APLAZAME_CART_DEFAULT_INSTALMENTS" value="' . Configuration::get('APLAZAME_CART_DEFAULT_INSTALMENTS') . '">',
                        ),
                        array(
                            'col' => 4,
                            'type' => 'text',
                            'prefix' => '<i class="icon icon-code"></i>',
                            'desc' => $this->l('CSS selector pointing to variable cart total price'),
                            'name' => 'APLAZAME_CART_CSS',
                            'label' => $this->l('Variable price CSS'),
                        ),
                        array(
                            'type' => $switch_or_radio,
                            'label' => $this->l('Enter maximum instalment'),
                            'name' => 'CART_WIDGET_MAX_DESIRED',
                            'is_bool' => true,
                            'desc' => $this->l('Allow the user to manually enter the maximum instalment they want to pay (only new widget)'),
                            'values' => array(
                                array(
                                    'id' => 'active_on',
                                    'value' => true,
                                ),
                                array(
                                    'id' => 'active_off',
                                    'value' => false,
                                ),
                            ),
                        ),
                        array(
                            'name' => 'CART_WIDGET_PRIMARY_COLOR',
                            'type' => 'color',
                            'label' => $this->l('Primary color'),
                            'desc' => $this->l('Primary color hexadecimal code for cart widget (only new widget)'),
                        ),
                        array(
                            'name' => 'CART_WIDGET_LAYOUT',
                            'type' => 'select',
                            'label' => $this->l('Layout'),
                            'desc' => $this->l('Layout of cart widget (only new widget)'),
                            'options' => array(
                                'query' => array(
                                    array(
                                        'id_option' => 'horizontal',
                                        'name' => $this->l('Horizontal'),
                                    ),
                                    array(
                                        'id_option' => 'vertical',
                                        'name' => $this->l('Vertical'),
                                    ),
                                ),
                                'id' => 'id_option',
                                'name' => 'name',
                            ),
                        ),
                        array(
                            'name' => 'CART_WIDGET_ALIGN',
                            'type' => 'select',
                            'label' => $this->l('Alignment'),
                            'desc' => $this->l('Cart widget alignment (only new widget)'),
                            'options' => array(
                                'query' => array(
                                    array(
                                        'id_option' => 'left',
                                        'name' => $this->l('Left'),
                                    ),
                                    array(
                                        'id_option' => 'center',
                                        'name' => $this->l('Center'),
                                    ),
                                    array(
                                        'id_option' => 'right',
                                        'name' => $this->l('Right'),
                                    ),
                                ),
                                'id' => 'id_option',
                                'name' => 'name',
                            ),
                        ),
                    ),
                ),
            );

        /**
         * Button form.
         */
        $button =
            array(
                'form' => array(
                    'legend' => array(
                        'title' => $this->l('Button'),
                        'icon' => 'icon-cogs',
                    ),
                    'input' => array(
                        array(
                            'col' => 4,
                            'type' => 'text',
                            'prefix' => '<i class="icon icon-code"></i>',
                            'desc' => $this->l('Aplazame Button Title'),
                            'name' => 'APLAZAME_BUTTON_TITLE',
                            'label' => $this->l('Button Title'),
                        ),
                        array(
                            'col' => 4,
                            'type' => (_PS_VERSION_ >= 1.7) ? 'textarea' : 'hidden',
                            'prefix' => '<i class="icon icon-code"></i>',
                            'desc' => $this->l('Aplazame Button Description'),
                            'name' => 'APLAZAME_BUTTON_DESCRIPTION',
                            'label' => $this->l('Button Description'),
                        ),
                        array(
                            'col' => 4,
                            'type' => 'text',
                            'prefix' => '<i class="icon icon-code"></i>',
                            'desc' => $this->l('Aplazame Button CSS Selector'),
                            'name' => 'APLAZAME_BUTTON',
                            'label' => $this->l('Button Selector'),
                        ),
                        array(
                            'col' => 4,
                            'type' => 'text',
                            'prefix' => '<i class="icon icon-code"></i>',
                            'desc' => $this->l('Aplazame Button Image that you want to show'),
                            'name' => 'APLAZAME_BUTTON_IMAGE',
                            'label' => $this->l('Button Image'),
                        ),
                    ),
                ),
            );

        /**
         * Developer settings form.
         */
        $dev_settings =
            array(
                'form' => array(
                    'legend' => array(
                        'title' => $this->l('Developer Settings (WARNING: DO NOT TOUCH IF NOT NECESSARY)'),
                        'icon' => 'icon-cogs',
                    ),
                    'input' => array(
                        array(
                            'type' => $switch_or_radio,
                            'label' => $this->l('Use v4 checkout API'),
                            'name' => 'APLAZAME_V4',
                            'is_bool' => true,
                            'desc' => $this->l('API version'),
                            'values' => array(
                                array(
                                    'id' => 'active_on',
                                    'value' => true,
                                ),
                                array(
                                    'id' => 'active_off',
                                    'value' => false,
                                ),
                            ),
                        ),
                    ),
                ),
            );

        /**
         * Save button.
         */
        $save_button = array('title' => $this->l('Save'));
        $button['form']['submit'] = $save_button;

        $form = array();
        $form[] = $settings;
        $form[] = $product_widget;
        $form[] = $cart_widget;
        $form[] = $button;
        $form[] = $dev_settings;

        return $form;
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

    public function hookActionOrderStatusUpdate($params)
    {
        $shipped = $params['newOrderStatus']->shipped;

        if ($shipped) {

            /** @var Order $order */
            $order = new Order($params['id_order']);

            if ($order->module == $this->name) {
                return $this->captureOrder($order);
            }
        }

        return false;
    }

    public function hookDisplayAdminProductsExtra($params)
    {
        if ($params && isset($params['id_product'])) {
            $id_product = $params['id_product'];
        } else {
            $id_product = Tools::getValue('id_product');
        }

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

        $aplazameJsUri = getenv('APLAZAME_JS_URI') ? getenv('APLAZAME_JS_URI') : 'https://cdn.aplazame.com/aplazame.js';

        $aplazameJsParams = http_build_query(array(
            'public_key' => Configuration::get('APLAZAME_PUBLIC_KEY'),
            'sandbox' => Configuration::get('APLAZAME_SANDBOX') ? 'true' : 'false',
        ));

        $this->context->smarty->assign(array(
            'aplazame_js_uri' => $aplazameJsUri,
            'aplazame_js_params' => $aplazameJsParams,
        ));

        return $this->display(__FILE__, 'header.tpl');
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
        if (!$this->isAvailable() || !Configuration::get('APLAZAME_CART_WIDGET_ENABLED')) {
            return false;
        }

        $language = Context::getContext()->language->iso_code;
        if ($language !== 'es') {
            return false;
        }

        /** @var Cart $cart */
        $cart = $params['cart'];
        $address = new Address($cart->id_address_invoice);
        $currency = new Currency($cart->id_currency);

        $this->context->smarty->assign(array(
            'aplazame_cart_total' => Aplazame_Sdk_Serializer_Decimal::fromFloat($cart->getOrderTotal())->value,
            'aplazame_currency_iso' => $currency->iso_code,
            'aplazame_css' => Configuration::get('APLAZAME_CART_CSS'),
            'aplazame_legal_advice' => Configuration::get('APLAZAME_CART_LEGAL_ADVICE') ? 'true' : 'false',
            'aplazame_downpayment_info' => Configuration::get('APLAZAME_CART_DOWNPAYMENT_INFO') ? 'true' : 'false',
            'aplazame_pay_in_4' => Configuration::get('APLAZAME_CART_PAY_IN_4'),
            'aplazame_default_instalments' => Configuration::get('APLAZAME_CART_DEFAULT_INSTALMENTS'),
            'aplazame_widget_out_of_limits' => Configuration::get('APLAZAME_WIDGET_OUT_OF_LIMITS'),
            'aplazame_widget_legacy' => Configuration::get('WIDGET_LEGACY'),
            'aplazame_max_desired' => Configuration::get('CART_WIDGET_MAX_DESIRED') ? 'true' : 'false',
            'aplazame_primary_color' => Configuration::get('CART_WIDGET_PRIMARY_COLOR'),
            'aplazame_layout' => Configuration::get('CART_WIDGET_LAYOUT'),
            'aplazame_align' => Configuration::get('CART_WIDGET_ALIGN'),
            'aplazame_customer_id' => $address->dni,
        ));

        return $this->display(__FILE__, 'shoppingcart.tpl');
    }

    /**
     * This method is used to render the payment button,
     * Take care if the button should be displayed or not.
     */
    public function hookDisplayPayment($params)
    {
        if (!$this->isAvailable()) {
            return false;
        }

        /** @var Cart $cart */
        $cart = $params['cart'];

        $this->context->smarty->assign(array(
            'aplazame' => array(
                'button_title' => Configuration::get('APLAZAME_BUTTON_TITLE'),
                'button_image' => Configuration::get('APLAZAME_BUTTON_IMAGE'),
                'button' => $this->getButtonTemplateVars($cart),
            ),
        ));

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
        $this->context->smarty->assign(array(
            'aplazame_button' => $this->getButtonTemplateVars($cart),
            'aplazame_description' => Configuration::get('APLAZAME_BUTTON_DESCRIPTION'),
        ));

        $newOption = new PrestaShop\PrestaShop\Core\Payment\PaymentOption();
        $newOption->setCallToActionText(Configuration::get('APLAZAME_BUTTON_TITLE'))
            ->setModuleName($this->name)
            ->setAction($link->getModuleLink($this->name, 'redirect'))
            ->setAdditionalInformation($this->fetch('module:aplazame/views/templates/hook/payment_1.7.tpl'))
            ->setLogo(Configuration::get('APLAZAME_BUTTON_IMAGE'))
        ;

        return array($newOption);
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

        if ($order->hasBeenPaid()) {
            return $this->display(__FILE__, 'confirmation_success.tpl');
        }

        $currentState = $order->getCurrentState();
        if ($currentState === Configuration::get(self::ORDER_STATE_PENDING)) {
            return $this->display(__FILE__, 'confirmation_pending.tpl');
        }

        return $this->display(__FILE__, 'confirmation_failure.tpl');
    }

    public function getWidget($params)
    {
        if (!$this->isAvailable() || !Configuration::get('APLAZAME_PRODUCT_WIDGET_ENABLED')) {
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

        $language = Context::getContext()->language->iso_code;
        if ($language !== 'es') {
            return false;
        }

        /** @var Cart $cart */
        $cart = $this->context->cart;
        $address = new Address($cart->id_address_invoice);
        $currency = Context::getContext()->currency;
        $this->context->smarty->assign(array(
            'aplazame_amount' => Aplazame_Sdk_Serializer_Decimal::fromFloat($product->getPrice(true, null, 2))->value,
            'aplazame_currency_iso' => $currency->iso_code,
            'aplazame_css' => Configuration::get('APLAZAME_PRODUCT_CSS'),
            'aplazame_article_id' => $product->id,
            'aplazame_legal_advice' => Configuration::get('APLAZAME_PRODUCT_LEGAL_ADVICE') ? 'true' : 'false',
            'aplazame_downpayment_info' => Configuration::get('APLAZAME_PRODUCT_DOWNPAYMENT_INFO') ? 'true' : 'false',
            'aplazame_pay_in_4' => Configuration::get('APLAZAME_PRODUCT_PAY_IN_4'),
            'aplazame_default_instalments' => Configuration::get('APLAZAME_PRODUCT_DEFAULT_INSTALMENTS'),
            'aplazame_widget_out_of_limits' => Configuration::get('APLAZAME_WIDGET_OUT_OF_LIMITS'),
            'aplazame_widget_legacy' => Configuration::get('WIDGET_LEGACY'),
            'aplazame_max_desired' => Configuration::get('PRODUCT_WIDGET_MAX_DESIRED') ? 'true' : 'false',
            'aplazame_primary_color' => Configuration::get('PRODUCT_WIDGET_PRIMARY_COLOR'),
            'aplazame_layout' => Configuration::get('PRODUCT_WIDGET_LAYOUT'),
            'aplazame_align' => Configuration::get('PRODUCT_WIDGET_ALIGN'),
            'aplazame_border' => Configuration::get('PRODUCT_WIDGET_BORDER') ? 'true' : 'false',
            'aplazame_customer_id' => $address->dni,
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

    public function callToRest($method, $path, $values = null, $apiVersion = 1)
    {
        $client = $this->getApiClient();

        try {
            return $client->request($method, $path, $values, $apiVersion);
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

    public function doRefund(Order $order, $amount)
    {
        $refund = function ($mid) use ($amount) {
            $decimal = Aplazame_Sdk_Serializer_Decimal::fromFloat($amount)->value;
            $this->callToRest('POST', '/orders/' . $mid . '/refund-extended', array('amount' => $decimal));
        };

        try {
            $refund($order->reference);
        } catch (Aplazame_Sdk_Api_ApiClientException $e) {
            $refund($order->id_cart);
        }
    }

    public function refundAmount(Order $order, $amount)
    {
        try {
            $this->doRefund($order, $amount);
        } catch (Exception $e) {
            $this->log(self::LOG_CRITICAL, 'Cannot refund. Detail ' . $e->getMessage(), $order->id_cart);

            return false;
        }

        if (_PS_VERSION_ >= '1.7.7') {
            return true;
        }

        return $order->addOrderPayment(-$amount, $this->displayName);
    }

    public function captureOrder(Order $order)
    {
        $reference = $order->reference;

        try {
            $payload = $this->callToRest('GET', '/orders/' . $reference . '/captures');
        } catch (Exception $e) {
            $this->log(self::LOG_CRITICAL, 'Cannot retrieve capture. Detail ' . $e->getMessage(), $reference);

            return false;
        }

        if ($payload['remaining_capture_amount'] != 0) {
            try {
                $response = $this->callToRest('POST', '/orders/' . $reference . '/captures', array('amount' => $payload['remaining_capture_amount']));
            } catch (Exception $e) {
                $this->log(self::LOG_CRITICAL, 'Cannot do capture. Detail ' . $e->getMessage(), $reference);

                return false;
            }

            return $response;
        }

        return false;
    }

    /**
     * @param Cart $cart
     *
     * @return array
     *
     * @throws Exception
     */
    public function createCheckoutOnAplazame(Cart $cart)
    {
        $checkout = Aplazame_Aplazame_BusinessModel_Checkout::createFromCart($cart, (int) $this->id, $this->currentOrder);

        return $this->callToRest(
            'POST',
            '/checkout',
            Aplazame_Sdk_Serializer_JsonSerializer::serializeValue($checkout),
            Configuration::get('APLAZAME_V4') ? 4 : 3
        );
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
        $tab->module = $this->name;

        return (boolean) $tab->add();
    }

    private function getButtonTemplateVars(Cart $cart)
    {
        $currency = new Currency((int) ($cart->id_currency));

        return array(
            'selector' => Configuration::get('APLAZAME_BUTTON'),
            'currency' => $currency->iso_code,
            'amount' => Aplazame_Sdk_Serializer_Decimal::fromFloat($cart->getOrderTotal())->value,
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

<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

include_once(_PS_MODULE_DIR_ . '/aplazame/api/Serializers.php');
require_once(dirname(__FILE__) . '/api/RestClient.php');

class Aplazame extends PaymentModule
{
    protected $config_form = false;

    const _version = '1.0.11';
    const API_CHECKOUT_PATH = '/orders';

    public function __construct()
    {
        $this->name = 'aplazame';
        if (!isset($this->local_path) || empty($this->local_path)) {
            $this->local_path = _PS_MODULE_DIR_.$this->name.'/';
        }
        $this->tab = 'payments_gateways';
        $this->version = self::_version;
        $this->author = 'Aplazame';
        $this->need_instance = 0;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Aplazame: compra ahora, paga después');
        $this->description = $this->l('Financiamos las compras a clientes y aumentamos un 18% las ventas en tu ecommerce.');

        $this->confirmUninstall = $this->l('¿Estás seguro de desinstalar el módulo?');
		
        #Fix for PrestaShop bug on 1.5.4.X that not appear the payment method
        if (_PS_VERSION_ > 1.5) {
                $this->limited_countries = array('ES');
                $this->limited_currencies = array('EUR');
        }
        $this->type = 'addonsPartner';
        $this->description_full = 'PAGA COMO QUIERAS<br/>

Tu decides cuándo y cómo quieres pagar todas tus compras de manera fácil, cómoda y segura.';
        $this->additional_description = "";
        $this->img = $this->_path . '/img/logo.png';
        $this->url = 'https://aplazame.com';
    }

    public function install()
    {
        if (extension_loaded('curl') == false) {
            $this->_errors[] = $this->l('You have to enable the cURL extension on your server to install this module');
            return false;
        }

        $iso_code = Country::getIsoById(Configuration::get('PS_COUNTRY_DEFAULT'));

        if (in_array($iso_code, $this->limited_countries) == false) {
            $this->_errors[] = $this->l('This module is not available in your country');
            return false;
        }

        Configuration::updateValue('APLAZAME_SANDBOX', false);
        Configuration::updateValue('APLAZAME_ENABLE_COOKIES', true);
        Configuration::updateValue('APLAZAME_HOST', 'https://aplazame.com');
        Configuration::updateValue('APLAZAME_BUTTON_IMAGE', 'white-148x46');
        Configuration::updateValue('APLAZAME_BUTTON', '#aplazame_payment_button');
        Configuration::updateValue('APLAZAME_WIDGET_PROD', "0");
        
        return parent::install() &&
                $this->registerHook('payment') &&
                $this->registerHook('paymentReturn') &&
                $this->registerHook('actionProductCancel') &&
                $this->registerHook('actionOrderDetail') &&
                $this->registerHook('actionOrderStatusPostUpdate') &&
                $this->registerHook('actionOrderStatusUpdate') &&
                $this->registerHook('actionPaymentConfirmation') &&
                $this->registerHook('actionValidateOrder') &&
                $this->registerHook('displayBeforePayment') &&
                $this->registerHook('displayHeader') &&
                $this->registerHook('displayAdminOrder') &&
                $this->registerHook('displayOrderConfirmation') &&
                $this->registerHook('displayPayment') &&
                $this->registerHook('displayProductButtons') &&
                $this->registerHook('displayShoppingCart') &&
                $this->registerHook('displayRightColumnProduct') &&
                $this->registerHook('displayRightColumn') &&
                $this->registerHook('displayAdminProductsExtra') &&
                $this->registerHook('displayAdminProductsListBefore') &&
                $this->registerHook('displayPaymentReturn') &&
                $this->registerController('AdminAplazameApiProxy', 'Aplazame API Proxy')
        ;
    }

    public function uninstall()
    {
        Configuration::deleteByName('APLAZAME_SANDBOX');


        return parent::uninstall();
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        /**
         * If values have been submitted in the form, process.
         */
        $style15 = '<style>
                label[for="active_on"],label[for="active_off"]{
                    float: none
                }
                </style>';

        if (((bool) Tools::isSubmit('submitAplazameModule')) == true) {
            $this->_postProcess();
        }

        $this->context->smarty->assign('module_dir', $this->_path);

        $output = $this->context->smarty->fetch($this->local_path . 'views/templates/admin/configure.tpl');

        if (_PS_VERSION_ < 1.6) {
            $output .= $style15;
        }

        return $output . $this->renderForm();
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
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
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
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
                                'label' => $this->l('Yes')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('No')
                            )
                        ),
                    ),
                    array(
                        'col' => 4,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-link"></i>',
                        'desc' => $this->l('Aplazame Host'),
                        'name' => 'APLAZAME_HOST',
                        'label' => $this->l('Host'),
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
                        'col' => 4,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-key"></i>',
                        'name' => 'APLAZAME_SECRET_KEY',
                        'label' => $this->l('Secret API Key'),
                        'desc' => $this->l('Aplazame Secret Key'),
                    ),
                    array(
                        'col' => 4,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-key"></i>',
                        'desc' => $this->l('Aplazame Public Key'),
                        'name' => 'APLAZAME_PUBLIC_KEY',
                        'label' => $this->l('Public API Key'),
                    ),
                    array(
                        'type' => (_PS_VERSION_ >= 1.6) ? 'switch' : 'radio',
                        'label' => $this->l('Enable Cookies'),
                        'name' => 'APLAZAME_ENABLE_COOKIES',
                        'is_bool' => true,
                        'desc' => $this->l('If you want to enable cookie tracking.'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
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
                                    'name' => $this->l('displayProductButtons')
                                ),
                                array(
                                    'id_option' => 1,
                                    'name' => $this->l('displayRightColumnProduct')
                                ),
                                array(
                                    'id_option' => 2,
                                    'name' => $this->l('displayRightColumn')
                                ),
                            ),
                            'id' => 'id_option',
                            'name' => 'name'
                        )
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        return array(
            'APLAZAME_SANDBOX' => Configuration::get('APLAZAME_SANDBOX', null),
            'APLAZAME_HOST' => Configuration::get('APLAZAME_HOST', null),
            'APLAZAME_BUTTON' => Configuration::get('APLAZAME_BUTTON', null),
            'APLAZAME_SECRET_KEY' => Configuration::get('APLAZAME_SECRET_KEY', null),
            'APLAZAME_PUBLIC_KEY' => Configuration::get('APLAZAME_PUBLIC_KEY', null),
            'APLAZAME_BUTTON_IMAGE' => Configuration::get('APLAZAME_BUTTON_IMAGE', null),
            'APLAZAME_ENABLE_COOKIES' => Configuration::get('APLAZAME_ENABLE_COOKIES', null),
            'APLAZAME_WIDGET_PROD' => Configuration::get('APLAZAME_WIDGET_PROD', null),
        );
    }

    /**
     * Save form data.
     */
    protected function _postProcess()
    {
        $form_values = $this->getConfigFormValues();

        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }
    }

    /**
     * This method is used to render the payment button,
     * Take care if the button should be displayed or not.
     */
    public function hookPayment($params)
    {
        $currency_id = $params['cart']->id_currency;
        $currency = new Currency((int) $currency_id);

        if (in_array($currency->iso_code, $this->limited_currencies) == false) {
            return false;
        }

        $this->assignSmartyVars(array('module_dir'=> $this->_path));

        $this->assignSmartyVars(array(
            'aplazame_host' => Configuration::get('APLAZAME_HOST', null),
            'aplazame_button' => Configuration::get('APLAZAME_BUTTON', null),
            'aplazame_currency_iso' => $currency->iso_code,
            'aplazame_cart_total' => self::formatDecimals($params['cart']->getOrderTotal()),
            'aplazame_button_image' => Configuration::get('APLAZAME_BUTTON_IMAGE', null),
        ));
        return $this->display(__FILE__, 'views/templates/hook/payment.tpl');
    }

    /**
     * This hook is used to display the order confirmation page.
     */
    public function hookPaymentReturn($params)
    {
        //Add conditionals for a bad ERP Connector that calls to this method without parameters
        if ($this->active == false || !isset($params['objOrder']) || !Validate::isLoadedObject($params['objOrder'])) {
            return;
        }

        $order = $params['objOrder'];
        
        if ($order->getCurrentOrderState()->id != Configuration::get('PS_OS_ERROR')) {
            $this->assignSmartyVars(array('status'=> 'ok'));
        }

        $this->assignSmartyVars(array(
            'reference' => $order->reference,
        ));

        return $this->display(__FILE__, 'views/templates/hook/confirmation.tpl');
    }

    public function hookActionOrderDetail()
    {
    }

    public function refundAmount($Order, $amount)
    {
        $price_refund = $this->formatDecimals($amount);
        $result = $this->callToRest('GET', self::API_CHECKOUT_PATH . '?mid=' . $Order->id_cart);
        $result['response'] = json_decode($result['response'], true);
        if ($result['code'] == '200' && isset($result['response']['results'][0]['id'])) {
            $resultOrder = $this->callToRest('POST', self::API_CHECKOUT_PATH . '/' . $result['response']['results'][0]['mid'] . '/refund', array('amount' => $price_refund));
            if ($resultOrder['code'] != '200') {
                $this->logError('Error: Cannot refund order #'.$Order->id_cart.' - ID AP: '.$result['response']['results'][0]['id']);
            }
        } else {
            $this->logError('Error: Cannot refund order mid #'.$Order->id_cart.' not exists on Aplazame');
        }
    }
    public function hookActionProductCancel($params)
    {
        if (!Tools::isSubmit('generateDiscount') && !Tools::isSubmit('generateCreditSlip')) {
            $result = $this->callToRest('GET', self::API_CHECKOUT_PATH . '?mid=' . $params['order']->id_cart);
            $result['response'] = json_decode($result['response'], true);
            if ($result['code'] == '200' && isset($result['response']['results'][0]['id'])) {
                $checkout_data = $this->getCheckoutSerializer($params['order']->id, false);
                $order_data = array('order'=>$checkout_data['order']);
                $order_data['order']['shipping'] = $checkout_data['shipping'];
                $resultOrder = $this->callToRest('PUT', self::API_CHECKOUT_PATH . '/' . $result['response']['results'][0]['mid'], $order_data);
                $resultOrder['response'] = json_decode($resultOrder['response'], true);
                if ($resultOrder['response']['success'] != 'true') {
                    $this->logError('Error: Cannot update order mid #'.$params['order']->id_cart.' - ID AP: '.$result['response']['results'][0]['id'].' with_response: '.json_encode($resultOrder).' with data: '.json_encode($order_data));
                } else {
                    $this->logError('Success on update order mid #'.$params['order']->id_cart.' - ID AP: '.$result['response']['results'][0]['id'].' with data: '.json_encode($order_data));
                }
            } else {
                $this->logError('Error: Cannot update order mid #'.$params['order']->id_cart.' not exists on Aplazame');
            }
        }
    }

    public function hookActionOrderStatusPostUpdate($params)
    {
        $id_order = $params['id_order'];
        $statusObject = $params['newOrderStatus'];
        $Order = new Order($id_order);

        if ($statusObject->id == _PS_OS_CANCELED_) {
            $result = $this->callToRest('GET', self::API_CHECKOUT_PATH . '?mid=' . $Order->id_cart);
            $result['response'] = json_decode($result['response'], true);
            if ($result['code'] == '200' && isset($result['response']['results'][0]['id'])) {
                $result = $this->callToRest('POST', self::API_CHECKOUT_PATH . '/' . $result['response']['results'][0]['mid'] . '/cancel');
                $result['response'] = json_decode($result['response'], true);
                if ($result['response']['success'] != 'true') {
                    $this->logError('Error: Cannot cancel order mid #'.$Order->id_cart.' - ID AP: '.$result['response']['results'][0]['id']);
                }
            } else {
                $this->logError('Error: Cannot cancel order mid #'.$Order->id_cart.' not exists on Aplazame');
            }
        }
    }

    public function hookDisplayAdminOrder($params) {
        //if (_PS_VERSION_ < 1.6) {
        $id_order = $params['id_order'];
        $Order = new Order($id_order);

        if ($Order->module == $this->name) {
            $result = $this->callToRest('GET', self::API_CHECKOUT_PATH . '?mid=' . $Order->id_cart);
            $result['response'] = json_decode($result['response'], true);
            if ($result['code'] == '200' && isset($result['response']['results'][0]['id'])) {
                $result = $this->callToRest('GET', self::API_CHECKOUT_PATH . '/' . $result['response']['results'][0]['id']);
                $result['response'] = json_decode($result['response'], true);

                if ($result['code'] == '200') {
                    $dataAplazame = array(
                        'uuid' => $result['response']['id'],
                        'mid' => $Order->id_cart
                    );

                    $this->assignSmartyVars(array(
                        'aplazame_data' => $dataAplazame,
                        'logo' => $this->img,
                    ));

                    return $this->display(__FILE__, 'views/templates/admin/order_16.tpl');
                } else {
                    $this->logError('Error: @2 #'.$id_order.' not exists on Aplazame #'.$result['code'] .'# '.var_export($result['response'], true));
                    return '<div class="error_aplazame" code="'.$result['code'] .'" style="display:none">'.var_export($result['response'], true).'</div>';
                }
            } else {
                $this->logError('Error: @1  #'.$id_order.' not exists on Aplazame #'.$result['code'] .'# '.var_export($result['response'], true));
            }
        }
        return '';
    }

    public function hookDisplayHeader()
    {
        if ($this->active == false) {
            return;
        }

        $this->assignSmartyVars(array(
            'aplazame_enabled_cookies' => Configuration::get('APLAZAME_ENABLE_COOKIES', null),
            'aplazame_host' => Configuration::get('APLAZAME_HOST', null),
            'aplazame_public_key' => Configuration::get('APLAZAME_PUBLIC_KEY', null),
            'aplazame_is_sandbox' => Configuration::get('APLAZAME_SANDBOX', null) ? 'true' : 'false',
        ));
        return $this->display(__FILE__, 'views/templates/hook/header.tpl');
    }

    public function hookDisplayOrderConfirmation($params)
    {
        return $this->hookPaymentReturn($params);
    }

    public function hookDisplayProductButtons($params)
    {
        $display_widget = (int)Configuration::get('APLAZAME_WIDGET_PROD', null);
        if($display_widget == 0 || empty($display_widget)){
            return $this->getProductWidget($params);
        }
        return false;
    }
    
    public function hookDisplayRightColumnProduct($params){
        $display_widget = (int)Configuration::get('APLAZAME_WIDGET_PROD', null);
        if($display_widget == 1){
            return $this->getProductWidget($params);
        }
        return false;
    }
    
    public function hookDisplayRightColumn($params){
        if (isset($this->context->controller->php_self) && $this->context->controller->php_self == 'product'){
            $display_widget = (int)Configuration::get('APLAZAME_WIDGET_PROD', null);
            if($display_widget == 2){
                return $this->getProductWidget($params);
            }
        }
        return false;
    }
    
    public function getProductWidget($params){
        if(isset($params['product'])){
            $product = $params['product'];
        }elseif(Tools::getValue('controller')=='product' && Tools::getValue('id_product')){
            $product = new Product(Tools::getValue('id_product'));
        }else{
            if (method_exists($this->context->controller, 'getProduct')){
                $product = $this->context->controller->getProduct();
            }
        }
        
        if (!isset($product) || !Validate::isLoadedObject($product)){
            return false; 
        }elseif(!$product->show_price || !$product->available_for_order){
            return false;
        }
        
        //Workaround for DevOps that change server locale
        $defaultLocate = setlocale(LC_ALL,"0");
        if($defaultLocate != 'C'){
                setlocale(LC_NUMERIC, 'en_US');
        }
        $this->assignSmartyVars(array(
            'product_aplazame_price' => self::formatDecimals($product->getPrice(true, null, 2))
        ));
        if($defaultLocate != 'C'){
                setlocale(LC_ALL, $defaultLocate);
        }
        return $this->display(__FILE__, 'views/templates/hook/product.tpl');
    }
    
    public function hookDisplayPayment($params)
    {
        return $this->hookPayment($params);
    }

    public function hookDisplayPaymentReturn($params)
    {
        //PrestaShop hook duplication problem. We keep this if we show a error on a client
        return false;
        //return $this->hookPaymentReturn($params);
    }

    public static function formatDecimals($amount = 0)
    {
        $negative = false;
        $str = sprintf("%.2f", $amount);
        if (strcmp($str[0], "-") === 0) {
            $str = substr($str, 1);
            $negative = true;
        }
        $parts = explode(".", $str, 2);
        if ($parts === false) {
            return 0;
        }
        if (empty($parts)) {
            return 0;
        }
        if (strcmp($parts[0], 0) === 0 && strcmp($parts[1], "00") === 0) {
            return 0;
        }
        $retVal = "";
        if ($negative) {
            $retVal .= "-";
        }
        $retVal .= ltrim($parts[0] . substr($parts[1], 0, 2), "0");
        return intval($retVal);
    }

    public function getCheckoutSerializer($id_order = 0, $id_cart = 0)
    {
        $serializer = new Aplazame_Serializers();
        $Order = new Order($id_order);
        $Cart = false;
        if ($id_cart) {
            $Cart = new Cart($id_cart);
        }
        return $serializer->getCheckout($Order, $Cart);
    }

    public function getCustomerHistory(Customer $customer, $limit)
    {
        $serializer = new Aplazame_Serializers();
        return $serializer->getHistory($customer, $limit);
    }

    public function callToRest($method, $url, array $values = null)
    {
        if ($values) {
            $values = json_encode($values);
        }

        $versions = array(
            'PHP/' . PHP_VERSION,
            'Prestashop/' . _PS_VERSION_,
            'AplazamePrestashop/' . self::_version,
        );

        $url = trim(str_replace('://', '://api.', Configuration::get('APLAZAME_HOST', null)), "/") . $url;

        $headers = array();
        if (in_array($method, array(
                    'POST', 'PUT', 'PATCH', 'DELETE')) && $values) {
            $headers[] = 'Content-type: application/json';
        }

        $headers[] = 'Authorization: Bearer ' .
                Configuration::get('APLAZAME_SECRET_KEY', null);

        $headers[] = 'User-Agent: ' . implode(', ', $versions);

        $headers[] = 'Accept: ' . 'application/vnd.aplazame.' .
                (Configuration::get('APLAZAME_SANDBOX', null) ? 'sandbox.' : '') . 'v1+json';

        if (extension_loaded('curl') == false || $method == 'PUT') {
            $opts = array('http' =>
                array(
                    'method' => $method,
                    'header' => $headers,

                )
            );
            if ($values) {
                $opts['http']['content'] = $values;
            }

            $context = stream_context_create($opts);
            try {
                $response = file_get_contents($url, false, $context);
                $headersResponse = $this->parseHeaders($http_response_header);
                $result['response'] = $response;
                $result['code'] = $headersResponse['reponse_code'];
            } catch (Exception $e) {
                $this->logError($e->getMessage());
            }
        } else {
            try {
                $response = RestClient::$method($url, $values, null, null, null, $headers);
            } catch (Exception $e) {
                $this->logError($e->getMessage());
            }
            $result['response'] = $response->getResponse();
            $result['code'] = $response->getResponseCode();
        }

        return $result;
    }

    public function parseHeaders($headers)
    {
        $head = array();
        foreach ($headers as $k => $v) {
            $t = explode(':', $v, 2);
            if (isset($t[1])) {
                $head[trim($t[0])] = trim($t[1]);
            } else {
                $head[] = $v;
                if (preg_match("#HTTP/[0-9\.]+\s+([0-9]+)#", $v, $out)) {
                    $head['reponse_code'] = intval($out[1]);
                }
            }
        }
        return $head;
    }

    public function getErrorMessage($error_code)
    {
        $error = "An error occurred while processing payment";
        switch ($error_code) {
            case "400": $error = "Bad Request - The data have not been correctly validated";
                break;
            case "401": $error = "Unauthorized - Token is not found in the request or it is wrong";
                break;
            case "403": $error = "Forbidden - You do not have permission to do this operation";
                break;
            case "404": $error = "Not Found - The object or the resource is not found";
                break;
            case "405": $error = "Method Not Allowed - You tried to access with an invalid method";
                break;
            case "406": $error = "Not Acceptable - You requested a format that is not valid";
                break;
            case "429": $error = "Too Many Requests - Multiple simultaneous requests are made. Slown down!";
                break;
            case "500": $error = "Internal Server Error	Houston, we have a problem. Try again later.";
                break;
            case "503": $error = "Service Unavailable	We’re temporarially offline for maintanance. Please try again later.";
                break;
        }
        return $error;
    }

    public function logError($message)
    {
        file_put_contents(dirname(__FILE__) . '/logs/exception_log', PHP_EOL.date(DATE_ISO8601) . ' ' . $message . '\r\n', FILE_APPEND);
    }
    
    function validateController($id_order,$cancel_order=false,$custom_message=false){
        if(empty($id_order)){
            return false;
        }
        
        if($cancel_order){
            $result['code'] = '403';
            $cart = new Cart((int) $id_order);
            $cart_id = $id_order;
            $amount = $cart->getOrderTotal();
        }else{
            $result = $this->callToRest('POST', self::API_CHECKOUT_PATH . '/' . $id_order . '/authorize');
            $result['response'] = json_decode($result['response'], true);
            $cart_id = $result['response']['id'];
            $amount = $result['response']['amount'] / 100;
            $cart = new Cart((int) $cart_id);
            if(!Validate::isLoadedObject($cart)){
                //throw new Exception('Error processing order. KO - Cart not loaded. This symptom is maybe of WAF webservice protection. Please contact your server provider to take action.', 400);
                header('HTTP/1.1 400 Bad Request', true, 400);
                exit('Error processing order. KO - Cart not loaded. This symptom is maybe of WAF webservice protection. Please contact your server provider to take action '.  var_export($result,true));
            }
        }

        Context::getContext()->cart = new Cart((int) $cart_id);

        $customer_id = Context::getContext()->cart->id_customer;

        Context::getContext()->customer = new Customer((int) $customer_id);
        Context::getContext()->currency = new Currency((int) Context::getContext()->cart->id_currency);
        Context::getContext()->language = new Language((int) Context::getContext()->cart->id_lang);

        $secure_key = Context::getContext()->customer->secure_key;
        $module_name = $this->displayName;
        
        $order_id = Order::getOrderByCartId((int)$cart_id);
        if(!empty($order_id)){
            return false;
        }
        
        if ($this->isValidOrder($result['code']) === true && !$cancel_order) {
            $payment_status = Configuration::get('PS_OS_PAYMENT');
            $message = null;
            
            $currency_id = (int) Context::getContext()->currency->id;

            return $this->validateOrder($cart_id, $payment_status, $amount, $module_name, $message, array(), $currency_id, false, $secure_key);
        } else {
            $payment_status = Configuration::get('PS_OS_ERROR');
            $error = $this->getErrorMessage($result['code']);
            if($custom_message){
                $error = $custom_message;
            }
            if($cancel_order){
                $payment_status = Configuration::get('PS_OS_CANCELED');
            }
            $message = $this->l($error);
            $this->logError($message);
            $this->validateOrder($cart_id, $payment_status, $amount, $module_name, $message, array(), $currency_id, false, $secure_key);
            if($cancel_order){
                return true;
            }
            return false;
        }
    }

    public function assignSmartyVars($array)
    {
        if (_PS_VERSION_ >= 1.6 || isset($this->smarty)) {
            $this->smarty->assign($array);
        } else {
            $this->context->smarty->assign($array);
        }
    }

    protected function isValidOrder($code)
    {
        if ($code == '200') {
            return true;
        } else {
            return false;
        }
    }
    
    function duplicateCart($id_cart=false){
        $oldCart = new Cart(($id_cart)?$id_cart:Context::getContext()->cart->id);
        $data = $oldCart->duplicate();

        if($data['success']) {
            $cart = $data['cart'];
            Context::getContext()->cart = $cart;
            CartRule::autoAddToCart(Context::getContext());
            Context::getContext()->cookie->id_cart = $cart->id;
        } else {
            $this->logError('Error: Cannot duplicate cart '.Context::getContext()->cart->id);
        }
        
    }
    
    public function hookDisplayShoppingCart($params){
        // $params contiene realmente $this->context->cart->getSummaryDetails(null, true); 
        $this->assignSmartyVars(
            array(
                'total_aplazame_price' => self::formatDecimals($params['total_price']),
            )
        );
        return $this->display(__FILE__, 'views/templates/hook/shoppingcart.tpl');
    }
    
    public function hookDisplayAdminProductsExtra($params)
    {
        $id_product = Tools::getValue('id_product');

        $serializer = new Aplazame_Serializers();
        $articles = $serializer->getArticlesCampaign(array($id_product), $this->context->language->id);

        $this->assignSmartyVars(array(
            'articles' => $articles,
        ));
        
        return $this->display(__FILE__, 'views/templates/admin/product.tpl');
    }

    public function hookDisplayAdminProductsListBefore($params){
        if (!Tools::isSubmit('submitBulkassignProductsToAplazameCampaignsproduct')) {
            return false;
        }
        if (Tools::getIsset('cancel')) {
            return false;
        }

        $articlesId = Tools::getValue('productBox');

        $serializer = new Aplazame_Serializers();
        $articles = $serializer->getArticlesCampaign($articlesId, $this->context->language->id);

        $this->assignSmartyVars(array(
            'articles' => $articles,
            'old_presta' => (_PS_VERSION_ < 1.6),
        ));

        return $this->display(__FILE__, 'views/templates/admin/product_list.tpl');
    }
    
    public function getMerchant($only_id=false){
        $result = $this->callToRest('GET', '/merchants');
        $result['response'] = json_decode($result['response'], true);
        if(isset($result['response']['results'][0]['id']) && !empty($result['response']['results'][0]['id'])){
            $merchant = $result['response']['results'][0];
            if($only_id){
                return $merchant['id'];
            }
            return $merchant;
        }
        return false;
        
    }

    protected function registerController($className, $name)
    {
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

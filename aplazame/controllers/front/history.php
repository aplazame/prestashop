<?php

/**
 * @property Aplazame module
 */
class AplazameHistoryModuleFrontController extends ModuleFrontController
{
    public $limit_orders = 10;

    public function postProcess()
    {
        $auth = $this->getHeaderAuthorization();
        if (!$auth || $auth !== Configuration::get('APLAZAME_SECRET_KEY')) {
            $this->apiResponse(array('error' => 'Authorization not valid'));
        }

        $checkoutToken = Tools::getValue('checkout_token', false);
        if (!$checkoutToken) {
            $this->apiResponse(array('error' => 'missing checkout_token'));
        }

        $orderId = Order::getOrderByCartId($checkoutToken);
        $order = new Order($orderId);
        if (!Validate::isLoadedObject($order)) {
            $this->apiResponse(array('error' => 'mid not found'));
        }

        if ($order->module !== $this->module->name) {
            $this->apiResponse(array('error' => 'mid is not from an Aplazame order'));
        }

        $this->apiResponse($this->getCustomerHistory($order->id_customer, $this->limit_orders));
    }

    private function apiResponse($data)
    {
        exit(Tools::jsonEncode($data));
    }

    private function getHeaderAuthorization()
    {
        if (function_exists('getallheaders')) {
            $headers = getallheaders();
        } else {
            $headers = $this->getallheaders();
        }

        if (isset($headers['Authorization'])) {
            return trim(str_replace('Bearer', '', $headers['Authorization']));
        }

        return false;
    }

    private function getCustomerHistory($customerId, $limit)
    {
        $serializer = new AplazameSerializers();

        $orders = Db::getInstance()->executeS(
            'SELECT * FROM ' . _DB_PREFIX_ . 'orders'
            . ' WHERE id_customer = ' . $customerId
            . ' ORDER BY id_order DESC LIMIT ' . $limit
        );

        return $serializer->getHistory($orders);
    }

    private function getallheaders()
    {
        $headers = '';
        foreach ($_SERVER as $name => $value) {
            if (Tools::substr($name, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(Tools::strtolower(str_replace('_', ' ', Tools::substr($name, 5)))))] = $value;
            }
        }

        return $headers;
    }
}

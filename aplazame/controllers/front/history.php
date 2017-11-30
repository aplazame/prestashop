<?php
/**
 * This file is part of the official Aplazame module for PrestaShop.
 *
 * @author    Aplazame <soporte@aplazame.com>
 * @copyright 2015-2016 Aplazame
 * @license   see file: LICENSE
 */

/**
 * @property Aplazame module
 */
class AplazameHistoryModuleFrontController extends ModuleFrontController
{
    public $limit_orders = 10;

    public function postProcess()
    {
        $auth = $this->getAuthorizationFromRequest();
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

    private function getCustomerHistory($customerId, $limit)
    {
        $orders = Db::getInstance()->executeS(
            'SELECT * FROM ' . _DB_PREFIX_ . 'orders'
            . ' WHERE id_customer = ' . (int) $customerId
            . ' ORDER BY id_order DESC LIMIT ' . (int) $limit
        );

        $historyOrders = array();
        foreach ($orders as $orderData) {
            $historyOrders[] = Aplazame_Aplazame_Api_BusinessModel_HistoricalOrder::createFromOrder(new Order($orderData['id_order']));
        }

        return $historyOrders;
    }

    private function getAuthorizationFromRequest()
    {
        $token = Tools::getValue('access_token', null);
        if ($token) {
            return $token;
        }

        if (function_exists('getallheaders')) {
            $headers = getallheaders();
        } else {
            $headers = $this->getallheaders();
        }
        $headers = array_change_key_case($headers, CASE_LOWER);

        if (isset($headers['authorization'])) {
            return trim(str_replace('Bearer', '', $headers['authorization']));
        }

        return false;
    }

    private function getallheaders()
    {
        $headers = array();
        $copy_server = array(
            'CONTENT_TYPE'   => 'content-type',
            'CONTENT_LENGTH' => 'content-length',
            'CONTENT_MD5'    => 'content-md5',
        );

        foreach ($_SERVER as $name => $value) {
            if (Tools::substr($name, 0, 5) === 'HTTP_') {
                $name = Tools::substr($name, 5);
                if (!isset($copy_server[$name]) || !isset($_SERVER[$name])) {
                    $headers[str_replace(' ', '-', Tools::strtolower(str_replace('_', ' ', $name)))] = $value;
                }
            } elseif (isset($copy_server[$name])) {
                $headers[$copy_server[$name]] = $value;
            }
        }

        if (!isset($headers['authorization'])) {
            if (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
                $headers['authorization'] = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
            } elseif (isset($_SERVER['PHP_AUTH_USER'])) {
                $basic_pass = isset($_SERVER['PHP_AUTH_PW']) ? $_SERVER['PHP_AUTH_PW'] : '';
                $headers['authorization'] = 'Basic ' . base64_encode($_SERVER['PHP_AUTH_USER'] . ':' . $basic_pass);
            } elseif (isset($_SERVER['PHP_AUTH_DIGEST'])) {
                $headers['authorization'] = $_SERVER['PHP_AUTH_DIGEST'];
            }
        }

        return $headers;
    }
}

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
        if (!$this->verifyAuthentication()) {
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

    /**
     * @return bool
     */
    private function verifyAuthentication()
    {
        $privateKey = Configuration::get('APLAZAME_SECRET_KEY');

        $authorization = $this->getAuthorizationFromRequest();
        if (!$authorization || empty($privateKey)) {
            return false;
        }

        return ($authorization === $privateKey);
    }

    private function getAuthorizationFromRequest()
    {
        $token = Tools::getValue('access_token', null);
        if ($token) {
            return $token;
        }

        return false;
    }
}

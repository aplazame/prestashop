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
class AplazameConfirmationModuleFrontController extends ModuleFrontController
{
    public function postProcess()
    {
        if (!Module::isInstalled('aplazame') || !Module::isEnabled('aplazame')) {
            $this->error('Aplazame is not enabled');
        }

        $cartId = $mid = Tools::getValue('checkout_token');
        $secureKey = Tools::getValue('key', false);
        if (!$mid || !$secureKey) {
            $this->error('Missing required fields');
        }

        $cart = new Cart((int) $mid);
        if (!Validate::isLoadedObject($cart)) {
            $this->error('Cart does not exists or does not have an order');
        }

        try {
            $response = $this->module->callToRest('POST', '/orders/' . $mid . '/authorize');
        } catch (Exception $e) {
            $message = 'Aplazame Error ' . $e->getMessage();

            $this->module->validateOrder(
                $cartId,
                Configuration::get('PS_OS_ERROR'),
                $cart->getOrderTotal(true),
                $this->module->displayName,
                $message,
                null,
                null,
                false,
                $secureKey
            );

            $this->error('Authorization error');

            return;
        }

        $cartAmount = Aplazame_Sdk_Serializer_Decimal::fromFloat($cart->getOrderTotal(true))->value;
        if ($response['amount'] !== $cartAmount) {
            $this->error('Invalid');
        }

        if (!$this->module->validateOrder(
            $cartId,
            Configuration::get('PS_OS_PAYMENT'),
            $cart->getOrderTotal(true),
            $this->module->displayName,
            null,
            null,
            null,
            false,
            $secureKey
        )) {
            $this->error('Cannot validate order');
        }

        exit('success');
    }

    protected function error($message)
    {
        $this->module->log(Aplazame::LOG_WARNING, $message);

        header('HTTP/1.1 400 Bad Request', true, 400);
        exit($message);
    }
}

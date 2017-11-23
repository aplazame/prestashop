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
class AplazamePendingModuleFrontController extends ModuleFrontController
{
    public function postProcess()
    {
        if (!Module::isInstalled('aplazame') || !Module::isEnabled('aplazame')) {
            $this->goKo();
            return;
        }

        $mid = Tools::getValue('id_cart');
        $secureKey = Tools::getValue('key');
        if (!$mid || !$secureKey) {
            $this->goKo();
            return;
        }

        $cart = new Cart((int) $mid);
        if (!Validate::isLoadedObject($cart)) {
            $this->goKo();
            return;
        }

        if (!$this->module->validateOrder(
            $mid,
            Configuration::get(Aplazame::ORDER_STATE_PENDING),
            $cart->getOrderTotal(true),
            $this->module->displayName,
            'Waiting for Aplazame review',
            null,
            null,
            false,
            $secureKey
        )) {
            $this->goKo();
            return;
        }

        $this->goSuccess($cart, $secureKey);
    }

    private function goKo()
    {
        Tools::redirect('index.php?controller=order&step=1');
    }

    private function goSuccess($cart, $secureKey)
    {
        return Tools::redirect(
            'index.php?controller=order-confirmation&id_cart=' .
            (int)$cart->id .
            '&id_module=' .
            (int)$this->module->id .
            '&id_order=' .
            $this->module->currentOrder .
            '&key=' .
            $secureKey
        );
    }
}

<?php

/**
 * @property Aplazame module
 */
class AplazameCancelModuleFrontController extends ModuleFrontController
{
    public function postProcess()
    {
        $cartId = Tools::getValue('id_cart', false);
        if (!$cartId) {
            Tools::redirect('index.php?controller=order-confirmation');
        }

        $cart = new Cart((int) $cartId);
        if (!$cart->orderExists()) {
            $this->module->validateOrder(
                $cart->id,
                Configuration::get('PS_OS_CANCELED'),
                $cart->getOrderTotal(),
                $this->module->displayName,
                'Order cancelled by Aplazame cancel_url',
                null,
                null,
                false,
                Tools::getValue('key', false)
            );
        }

        $orderId = Order::getOrderByCartId($cart->id);
        Tools::redirect(
            'index.php?controller=order-confirmation&id_cart=' . $cart->id
            . '&id_module=' . $this->module->id
            . '&id_order=' . $orderId
            . '&key=' . $cart->secure_key
        );
    }
}

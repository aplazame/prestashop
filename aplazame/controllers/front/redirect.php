<?php
/**
 * @copyright 2015-2016 Aplazame
 * @license   see file: LICENSE
 */

/**
 * @property Aplazame module
 */
class AplazameRedirectModuleFrontController extends ModuleFrontController
{
    public function postProcess()
    {
        $cart = Context::getContext()->cart;

        if ($this->orderExists($cart->id)) {
            $this->module->log(
                Aplazame::LOG_INFO,
                'Cart already exists in Aplazame. Create a new one with a different ID',
                $cart->id
            );
            $cart = $this->duplicateCart($cart);
        }

        $this->context->smarty->assign(array(
            'aplazame_order' => AplazameSerializers::getCheckout($cart, (int) $this->module->id, $this->module->currentOrder),
        ));

        if (_PS_VERSION_ < 1.7) {
            return $this->setTemplate('redirect_1.5.tpl');
        }

        return $this->setTemplate('module:aplazame/views/templates/front/redirect_1.7.tpl');
    }

    /**
     * @param string $mid
     *
     * @return bool
     */
    private function orderExists($mid)
    {
        try {
            $response = $this->module->callToRest('GET', '/orders?mid=' . $mid);
        } catch (Exception $e) {
            return false;
        }

        if ($response['is_error'] || empty($response['payload']['results'])) {
            return false;
        }

        return true;
    }

    private function duplicateCart(Cart $oldCart)
    {
        $data = $oldCart->duplicate();
        if (!$data || !$data['success']) {
            $this->module->log(Aplazame::LOG_WARNING, 'Cannot duplicate cart', $oldCart->id);

            return $oldCart;
        }

        $cart = $data['cart'];
        $this->context->cookie->id_cart = $cart->id;
        $this->context->cart = $cart;
        CartRule::autoAddToCart($this->context);
        $this->context->cookie->write();

        return $cart;
    }
}

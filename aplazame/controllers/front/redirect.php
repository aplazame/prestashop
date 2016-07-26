<?php

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
            $this->duplicateCart($cart);
        }

        $serializer = new Aplazame_Serializers();

        $this->context->smarty->assign(array(
            'aplazame_order' => $serializer->getCheckout($cart, (int) $this->module->id, $this->module->currentOrder),
        ));

        return $this->setTemplate('redirect.tpl');
    }

    /**
     * @param string $mid
     *
     * @return bool
     */
    private function orderExists($mid)
    {
        $response = $this->module->callToRest('GET', '/orders?mid=' . $mid);
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

            return;
        }

        $cart = $data['cart'];
        Context::getContext()->cart = $cart;
        CartRule::autoAddToCart(Context::getContext());
        Context::getContext()->cookie->id_cart = $cart->id;
    }
}

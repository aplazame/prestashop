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
class AplazameRedirectModuleFrontController extends ModuleFrontController
{
    public function postProcess()
    {
        $cart = Context::getContext()->cart;
        if (!$cart->id) {
            Tools::redirect('index.php?controller=order');
        }

        $checkout = Aplazame_Aplazame_BusinessModel_Checkout::createFromCart($cart, (int) $this->module->id, $this->module->currentOrder);
        $this->context->smarty->assign(array(
            'aplazame_order' => Aplazame_Sdk_Serializer_JsonSerializer::serializeValue($checkout),
        ));

        if (_PS_VERSION_ < 1.7) {
            return $this->setTemplate('redirect_1.5.tpl');
        }

        return $this->setTemplate('module:aplazame/views/templates/front/redirect_1.7.tpl');
    }
}

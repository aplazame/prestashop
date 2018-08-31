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

        try {
            $payload = $this->module->createCheckoutOnAplazame($cart);
        } catch (Aplazame_Sdk_Api_ApiClientException $e) {
            $this->errors[] = 'Aplazame Error: ' . $e->getMessage();

            if (method_exists($this, 'redirectWithNotifications')) {
                $this->redirectWithNotifications('index.php?controller=order');

                return;
            }

            $this->setTemplate('display_errors.tpl');

            return;
        }

        $this->context->smarty->assign(array(
            'aid' => $payload['id'],
        ));

        if (_PS_VERSION_ < 1.7) {
            $this->setTemplate('redirect_1.5.tpl');
        }

        $this->setTemplate('module:aplazame/views/templates/front/redirect_1.7.tpl');
    }
}

<?php
/**
 * This file is part of the official Aplazame module for PrestaShop.
 *
 * @author    Aplazame <soporte@aplazame.com>
 * @copyright 2015-2016 Aplazame
 * @license   see file: LICENSE
 */

/**
 * Checkout.
 */
class Aplazame_Aplazame_BusinessModel_Checkout
{
    public static function createFromCart(Cart $cart, $moduleId, $orderId)
    {
        /** @var Aplazame $aplazame */
        $aplazame = ModuleCore::getInstanceByName('aplazame');

        $link = Context::getContext()->link;
        $confirmationQuery = array(
            'id_cart' => $cart->id,
            'key' => $cart->secure_key,
        );
        $successQuery = array(
            'id_cart' => $cart->id,
            'id_module' => $moduleId,
            'id_order' => $orderId,
            'key' => $cart->secure_key,
        );

        $merchant = new stdClass();
        $merchant->confirmation_url = $link->getModuleLink('aplazame', 'confirmation', $confirmationQuery);
        $merchant->cancel_url = $link->getPageLink('order');
        $merchant->success_url = $link->getPageLink('order-confirmation', null, null, $successQuery);
        $merchant->checkout_url = $link->getPageLink('order');

        $checkout = new self();
        $checkout->toc = true;
        $checkout->merchant = $merchant;
        $checkout->order = Aplazame_Aplazame_BusinessModel_Order::createFromCart($cart);
        $checkout->customer = Aplazame_Aplazame_BusinessModel_Customer::createFromCustomer(new Customer($cart->id_customer));
        $checkout->billing = Aplazame_Aplazame_BusinessModel_Address::createFromAddress(new Address($cart->id_address_invoice));
        $checkout->shipping = Aplazame_Aplazame_BusinessModel_ShippingInfo::createFromCart($cart);
        $checkout->meta = array(
            'module' => array(
                'name' => 'aplazame:prestashop',
                'version' => $aplazame->version,
            ),
            'version' => _PS_VERSION_,
        );

        return $checkout;
    }
}

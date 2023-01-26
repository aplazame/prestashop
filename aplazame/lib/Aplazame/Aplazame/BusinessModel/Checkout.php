<?php
/**
 * This file is part of the official Aplazame module for PrestaShop.
 *
 * @author    Aplazame <soporte@aplazame.com>
 * @copyright 2015-2023 Aplazame
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
        $successQuery = array(
            'id_cart' => $cart->id,
            'id_module' => $moduleId,
            'id_order' => $orderId,
            'key' => $cart->secure_key,
        );

        $merchant = new stdClass();
        $merchant->ko_url = $link->getPageLink('order');
        $merchant->success_url = $link->getPageLink('order-confirmation', null, null, $successQuery);
        $merchant->pending_url = $merchant->success_url;
        $merchant->dismiss_url = $link->getPageLink('order');
        $merchant->notification_url = $link->getModuleLink($aplazame->name, 'api', array('path' => '/confirm/', 'cart_id' => $cart->id));

        $checkout = new self();
        $checkout->toc = true;
        $checkout->merchant = $merchant;
        $checkout->order = Aplazame_Aplazame_BusinessModel_Order::createFromCart($cart);
        $checkout->customer = Aplazame_Aplazame_BusinessModel_Customer::createFromCustomer(new Customer($cart->id_customer));
        $checkout->billing = Aplazame_Aplazame_BusinessModel_Address::createFromAddress(new Address($cart->id_address_invoice));

        if (!$cart->isVirtualCart()) {
            $checkout->shipping = Aplazame_Aplazame_BusinessModel_ShippingInfo::createFromCart($cart);
        }

        $checkout->meta = Aplazame_Aplazame_BusinessModel_Meta::create();

        return $checkout;
    }
}

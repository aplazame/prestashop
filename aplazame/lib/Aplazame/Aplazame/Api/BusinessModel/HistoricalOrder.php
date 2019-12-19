<?php
/**
 * This file is part of the official Aplazame module for PrestaShop.
 *
 * @author    Aplazame <soporte@aplazame.com>
 * @copyright 2015-2019 Aplazame
 * @license   see file: LICENSE
 */

class Aplazame_Aplazame_Api_BusinessModel_HistoricalOrder
{
    public static function createFromOrder(Order $order)
    {
        $cart = new Cart($order->id_cart);
        $payment_method = $order->module;
        $status = $order->getCurrentOrderState();

        if ($status->id == Configuration::get('PS_OS_CANCELED')) {
            return self::createFromCart($cart, $payment_method, 'cancelled', 'cancelled');
        }

        if ($status->paid) {
            if ($order->hasProductReturned()) {
                return self::createFromCart($cart, $payment_method, 'refunded', 'refunded');
            }

            if ($status->shipped) {
                return self::createFromCart($cart, $payment_method, 'payed', 'completed');
            }

            return self::createFromCart($cart, $payment_method, 'payed', 'processing');
        }

        return self::createFromCart($cart, $payment_method, 'pending', 'payment');
    }

    public static function createFromCart(Cart $cart, $payment_method = 'none', $payment_status = 'none', $status = 'cart')
    {
        $serialized = array(
            'customer' => Aplazame_Aplazame_BusinessModel_Customer::createFromCustomer(new Customer($cart->id_customer)),
            'order' => Aplazame_Aplazame_BusinessModel_Order::createFromCart($cart, $cart->date_add),
            'billing' => Aplazame_Aplazame_BusinessModel_Address::createFromAddress(new Address($cart->id_address_invoice)),
            'meta' => Aplazame_Aplazame_BusinessModel_Meta::create(),
            'payment' => array(
                'method' => $payment_method,
                'status' => $payment_status,
            ),
            'status' => $status,
        );

        if (!$cart->isVirtualCart()) {
            $serialized['shipping'] = Aplazame_Aplazame_BusinessModel_ShippingInfo::createFromCart($cart);
        }

        return $serialized;
    }
}

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
        $currency = new Currency($order->id_currency);
        $status = $order->getCurrentStateFull(Context::getContext()->language->id);
        $amount = $order->getTotalPaid();
        $due = (!$amount) ? $cart->getOrderTotal(true) : 0;

        $serialized = array(
            'id' => $order->id_cart,
            'amount' => Aplazame_Sdk_Serializer_Decimal::fromFloat($amount),
            'due' => Aplazame_Sdk_Serializer_Decimal::fromFloat($due),
            'status' => $status['name'],
            'type' => $order->module,
            'order_date' => Aplazame_Sdk_Serializer_Date::fromDateTime(new DateTime($order->date_add)),
            'currency' => $currency->iso_code,
            'billing' => Aplazame_Aplazame_BusinessModel_Address::createFromAddress(new Address($order->id_address_invoice)),
        );

        if (!$cart->isVirtualCart()) {
            $serialized['shipping'] = Aplazame_Aplazame_BusinessModel_ShippingInfo::createFromCart($cart);
        }

        return $serialized;
    }

    public static function createFromCart(Cart $cart)
    {
        $currency = new Currency($cart->id_currency);

        $serialized = array(
            'id' => $cart->id,
            'amount' => Aplazame_Sdk_Serializer_Decimal::fromFloat($cart->getOrderTotal(true)),
            'due' => 0,
            'status' => 'pending',
            'type' => 'cart',
            'order_date' => Aplazame_Sdk_Serializer_Date::fromDateTime(new DateTime($cart->date_add)),
            'currency' => $currency->iso_code,
            'billing' => Aplazame_Aplazame_BusinessModel_Address::createFromAddress(new Address($cart->id_address_invoice)),
        );

        if (!$cart->isVirtualCart()) {
            $serialized['shipping'] = Aplazame_Aplazame_BusinessModel_ShippingInfo::createFromCart($cart);
        }

        return $serialized;
    }
}

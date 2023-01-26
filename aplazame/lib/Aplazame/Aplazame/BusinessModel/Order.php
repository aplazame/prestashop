<?php
/**
 * This file is part of the official Aplazame module for PrestaShop.
 *
 * @author    Aplazame <soporte@aplazame.com>
 * @copyright 2015-2023 Aplazame
 * @license   see file: LICENSE
 */

/**
 * Order.
 */
class Aplazame_Aplazame_BusinessModel_Order
{
    public static function createFromCart(Cart $cart, $order_date = null)
    {
        $currency = new Currency($cart->id_currency);

        $total_shipping = $cart->getTotalShippingCost();
        $total_shipping_tax_exc = $cart->getTotalShippingCost(null, false);
        $shipping_tax = $total_shipping - $total_shipping_tax_exc;

        $base_total_tax_inc = $cart->getOrderTotal(true);
        $base_total_tax_exc = $cart->getOrderTotal(false);
        $order_tax = $base_total_tax_inc - $base_total_tax_exc - $shipping_tax;

        $aOrder = new self();
        $aOrder->id = $cart->id;
        $aOrder->currency = $currency->iso_code;
        $aOrder->total_amount = Aplazame_Sdk_Serializer_Decimal::fromFloat($base_total_tax_inc);
        $aOrder->tax_rate = $order_tax ? Aplazame_Sdk_Serializer_Decimal::fromFloat(100 * $order_tax / $base_total_tax_exc) : 0;
        $aOrder->articles = array_map(function (array $productData) use ($cart) {
            return Aplazame_Aplazame_BusinessModel_Article::createFromProductData($cart, $productData);
        }, $cart->getProducts());
        $aOrder->discount = Aplazame_Sdk_Serializer_Decimal::fromFloat($cart->getOrderTotal(false, Cart::ONLY_DISCOUNTS));

        if ($order_date) {
            $aOrder->created = Aplazame_Sdk_Serializer_Date::fromDateTime(new DateTime($order_date));
        }

        return $aOrder;
    }
}

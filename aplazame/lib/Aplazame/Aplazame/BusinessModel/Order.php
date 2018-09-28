<?php
/**
 * This file is part of the official Aplazame module for PrestaShop.
 *
 * @author    Aplazame <soporte@aplazame.com>
 * @copyright 2015-2018 Aplazame
 * @license   see file: LICENSE
 */

/**
 * Order.
 */
class Aplazame_Aplazame_BusinessModel_Order
{
    public static function createFromCart(Cart $cart)
    {
        $currency = new Currency($cart->id_currency);

        $aOrder = new self();
        $aOrder->id = $cart->id;
        $aOrder->currency = $currency->iso_code;
        $aOrder->total_amount = Aplazame_Sdk_Serializer_Decimal::fromFloat($cart->getOrderTotal(true));
        $aOrder->articles = array_map(array('Aplazame_Aplazame_BusinessModel_Article', 'crateFromProductData'), $cart->getProducts());
        $aOrder->discount = Aplazame_Sdk_Serializer_Decimal::fromFloat($cart->getOrderTotal(true, Cart::ONLY_DISCOUNTS));

        return $aOrder;
    }
}

<?php
/**
 * This file is part of the official Aplazame module for PrestaShop.
 *
 * @author    Aplazame <soporte@aplazame.com>
 * @copyright 2015-2016 Aplazame
 * @license   see file: LICENSE
 */

/**
 * Shipping info.
 */
class Aplazame_Aplazame_BusinessModel_ShippingInfo
{
    public static function createFromCart(Cart $cart)
    {
        $address = new Address($cart->id_address_delivery);
        $carrier = new Carrier($cart->id_carrier);

        $shippingInfo = new self();
        $shippingInfo->first_name = $address->firstname;
        $shippingInfo->last_name = $address->lastname;
        $shippingInfo->street = $address->address1;
        $shippingInfo->city = $address->city;
        $shippingInfo->state = State::getNameById($address->id_state);
        $shippingInfo->country = Country::getIsoById($address->id_country);
        $shippingInfo->postcode = $address->postcode;
        $shippingInfo->phone = $address->phone;
        $shippingInfo->alt_phone = $address->phone_mobile;
        $shippingInfo->address_addition = $address->address2;
        $shippingInfo->name = $carrier->name;
        $shippingInfo->price = Aplazame_Sdk_Serializer_Decimal::fromFloat($cart->getOrderTotal(false, Cart::ONLY_SHIPPING));
        $shippingInfo->tax_rate = Aplazame_Sdk_Serializer_Decimal::fromFloat($carrier->getTaxesRate($address));

        return $shippingInfo;
    }
}

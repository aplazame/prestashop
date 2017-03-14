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
        $customer = new Customer($cart->id_customer);
        /** @var Carrier[] $carriers */
        $carriers = Carrier::getCarriersForOrder(Address::getZoneById($cart->id_address_delivery), $customer->getGroups(), $cart, $carrier_error);
        $carrierNames = array();
        foreach ($carriers as $carrier) {
            $carrierNames[] = $carrier->name;
        }

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
        $shippingInfo->name = implode($carrierNames, ';');
        $shippingInfo->price = Aplazame_Sdk_Serializer_Decimal::fromFloat($cart->getOrderTotal(false, Cart::ONLY_SHIPPING));

        return $shippingInfo;
    }
}

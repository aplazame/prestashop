<?php
/**
 * This file is part of the official Aplazame module for PrestaShop.
 *
 * @author    Aplazame <soporte@aplazame.com>
 * @copyright 2015-2023 Aplazame
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
        $shippingInfo->name = implode(';', self::compileCarriersName($cart));

        $total_shipping_tax_exc = $cart->getTotalShippingCost(null, false);
        $shippingInfo->price = Aplazame_Sdk_Serializer_Decimal::fromFloat($total_shipping_tax_exc);

        if ($total_shipping_tax_exc > 0) {
            $total_shipping = $cart->getTotalShippingCost();
            $shipping_tax = $total_shipping - $total_shipping_tax_exc;
            $shippingInfo->tax_rate = Aplazame_Sdk_Serializer_Decimal::fromFloat(100 * $shipping_tax / $total_shipping_tax_exc);
        }

        return $shippingInfo;
    }

    private static function compileCarriersName(Cart $cart)
    {
        $carriersName = array();

        $deliveryOptions = $cart->getDeliveryOption();
        $deliveryOptionsList = $cart->getDeliveryOptionList();
        foreach ($deliveryOptions as $id_address => $key) {
            if (!isset($deliveryOptionsList[$id_address]) || !isset($deliveryOptionsList[$id_address][$key])) {
                continue;
            }

            foreach ($deliveryOptionsList[$id_address][$key]['carrier_list'] as $c) {
                $carriersName[] = $c['instance']->name;
            }
        }

        return $carriersName;
    }
}

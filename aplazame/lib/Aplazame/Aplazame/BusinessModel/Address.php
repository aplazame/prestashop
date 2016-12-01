<?php
/**
 * This file is part of the official Aplazame module for PrestaShop.
 *
 * @author    Aplazame <soporte@aplazame.com>
 * @copyright 2015-2016 Aplazame
 * @license   see file: LICENSE
 */

class Aplazame_Aplazame_BusinessModel_Address
{
    /**
     * @param Address $address
     *
     * @return self
     */
    public static function createFromAddress(Address $address)
    {
        $aAddress = new self();
        $aAddress->first_name = $address->firstname;
        $aAddress->last_name = $address->lastname;
        $aAddress->street = $address->address1;
        $aAddress->city = $address->city;
        $aAddress->state = State::getNameById($address->id_state);
        $aAddress->country = Country::getIsoById($address->id_country);
        $aAddress->postcode = $address->postcode;
        $aAddress->phone = $address->phone;
        $aAddress->alt_phone = $address->phone_mobile;
        $aAddress->address_addition = $address->address2;

        return $aAddress;
    }
}

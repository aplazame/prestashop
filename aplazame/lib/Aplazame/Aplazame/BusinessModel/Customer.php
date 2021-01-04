<?php
/**
 * This file is part of the official Aplazame module for PrestaShop.
 *
 * @author    Aplazame <soporte@aplazame.com>
 * @copyright 2015-2021 Aplazame
 * @license   see file: LICENSE
 */

/**
 * Customer.
 */
class Aplazame_Aplazame_BusinessModel_Customer
{
    public static function createFromCustomer(Customer $customer)
    {
        if ($customer->is_guest) {
            return self::createGuess($customer);
        }

        return self::createExisting($customer);
    }

    public static function createExisting(Customer $customer)
    {
        switch ($customer->id_gender) {
            case 1:
                $gender = 1;
                break;
            case 2:
                $gender = 2;
                break;
            default:
                $gender = 0;
        }

        $aCustomer = new self();
        $aCustomer->email = $customer->email;
        $aCustomer->type = 'e';
        $aCustomer->gender = $gender;
        $aCustomer->id = $customer->id;
        $aCustomer->first_name = $customer->firstname;
        $aCustomer->last_name = $customer->lastname;
        if ($customer->birthday && $customer->birthday != '0000-00-00') {
            $aCustomer->birthday = $customer->birthday;
        }
        $aCustomer->date_joined = Aplazame_Sdk_Serializer_Date::fromDateTime(new DateTime($customer->date_add));

        return $aCustomer;
    }

    public static function createGuess(Customer $customer)
    {
        $aCustomer = new self();
        $aCustomer->email = $customer->email;
        $aCustomer->type = 'g';
        $aCustomer->gender = 0;

        return $aCustomer;
    }
}

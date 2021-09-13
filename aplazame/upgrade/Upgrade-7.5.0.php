<?php
/**
 * This file is part of the official Aplazame module for PrestaShop.
 *
 * @author    Aplazame <soporte@aplazame.com>
 * @copyright 2015-2020 Aplazame
 * @license   see file: LICENSE
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_7_5_0(Aplazame $module)
{
    Configuration::updateValue('APLAZAME_PRODUCT_PAY_IN_4', false);
    Configuration::updateValue('APLAZAME_CART_PAY_IN_4', false);

    return true;
}

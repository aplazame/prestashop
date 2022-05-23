<?php
/**
 * This file is part of the official Aplazame module for PrestaShop.
 *
 * @author    Aplazame <soporte@aplazame.com>
 * @copyright 2015-2022 Aplazame
 * @license   see file: LICENSE
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_6_1_0(Aplazame $module)
{
    Configuration::updateValue('APLAZAME_PRODUCT_LEGAL_ADVICE', false);
    Configuration::updateValue('APLAZAME_CART_LEGAL_ADVICE', false);

    return true;
}

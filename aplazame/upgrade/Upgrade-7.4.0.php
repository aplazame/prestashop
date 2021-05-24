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

function upgrade_module_7_4_0(Aplazame $module)
{
    Configuration::updateValue('PRODUCT_WIDGET_ALIGN', 'center');
    Configuration::updateValue('CART_WIDGET_ALIGN', 'center');

    return true;
}

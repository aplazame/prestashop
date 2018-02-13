<?php
/**
 * This file is part of the official Aplazame module for PrestaShop.
 *
 * @author    Aplazame <soporte@aplazame.com>
 * @copyright 2015-2016 Aplazame
 * @license   see file: LICENSE
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_5_1_0(Aplazame $module)
{
    Configuration::updateValue('APLAZAME_PRODUCT_WIDGET_ENABLED', true);
    Configuration::updateValue('APLAZAME_CART_WIDGET_ENABLED', true);

    return true;
}

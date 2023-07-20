<?php
/**
 * This file is part of the official Aplazame module for PrestaShop.
 *
 * @author    Aplazame <soporte@aplazame.com>
 * @copyright 2015-2023 Aplazame
 * @license   see file: LICENSE
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_7_9_0(Aplazame $module)
{
    Configuration::updateValue('PRODUCT_WIDGET_MAX_DESIRED', false);
    Configuration::updateValue('CART_WIDGET_MAX_DESIRED', false);

    return true;
}

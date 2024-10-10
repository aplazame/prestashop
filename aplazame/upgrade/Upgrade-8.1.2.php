<?php
/**
 * This file is part of the official Aplazame module for PrestaShop.
 *
 * @author    Aplazame <soporte@aplazame.com>
 * @copyright 2015-2024 Aplazame
 * @license   see file: LICENSE
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_8_1_2(Aplazame $module)
{
    Configuration::deleteByName('PRODUCT_WIDGET_SMALL_SIZE');
    Configuration::deleteByName('CART_WIDGET_SMALL_SIZE');

    return true;
}

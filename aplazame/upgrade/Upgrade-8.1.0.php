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

function upgrade_module_8_1_0(Aplazame $module)
{
    /* Deprecate widget legacy option */
    Configuration::deleteByName('WIDGET_LEGACY');

    /* New widget selectors */
    Configuration::updateValue('PRODUCT_WIDGET_VER', 'v5');
    Configuration::updateValue('CART_WIDGET_VER', 'v5');

    /* Widget v5 options */
    Configuration::updateValue('PRODUCT_WIDGET_SLIDER', true);
    Configuration::updateValue('PRODUCT_WIDGET_SMALL_SIZE', false);
    Configuration::updateValue('CART_WIDGET_SLIDER', true);
    Configuration::updateValue('CART_WIDGET_SMALL_SIZE', false);

    return true;
}

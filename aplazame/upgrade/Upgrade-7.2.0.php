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

function upgrade_module_7_2_0(Aplazame $module)
{
    Configuration::updateValue('WIDGET_LEGACY', true);
    Configuration::updateValue('PRODUCT_WIDGET_PRIMARY_COLOR', '#334bff');
    Configuration::updateValue('PRODUCT_WIDGET_LAYOUT', 'horizontal');
    Configuration::updateValue('CART_WIDGET_PRIMARY_COLOR', '#334bff');
    Configuration::updateValue('CART_WIDGET_LAYOUT', 'horizontal');

    return true;
}

<?php
/**
 * This file is part of the official Aplazame module for PrestaShop.
 *
 * @author    Aplazame <soporte@aplazame.com>
 * @copyright 2015-2025 Aplazame
 * @license   see file: LICENSE
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_8_2_0(Aplazame $module)
{
    Configuration::updateValue('APLAZAME_WIDGET_COUNTRY', 'auto');
    Configuration::updateValue('APLAZAME_PRODUCT_ONLY_CSS', true);

    return true;
}

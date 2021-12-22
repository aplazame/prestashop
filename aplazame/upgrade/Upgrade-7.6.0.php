<?php
/**
 * This file is part of the official Aplazame module for PrestaShop.
 *
 * @author    Aplazame <soporte@aplazame.com>
 * @copyright 2015-2021 Aplazame
 * @license   see file: LICENSE
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_7_6_0(Aplazame $module)
{
    Configuration::updateValue('APLAZAME_WIDGET_OUT_OF_LIMITS', 'show');

    return true;
}

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

function upgrade_module_7_7_0(Aplazame $module)
{
    Configuration::updateValue('APLAZAME_CREATE_ORDER_AT_CHECKOUT', false);

    return true;
}

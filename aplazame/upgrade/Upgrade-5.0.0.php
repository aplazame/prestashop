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

function upgrade_module_5_0_0(Aplazame $module)
{
    $module->unregisterHook('actionOrderStatusPostUpdate');
    if (_PS_VERSION_ >= 1.7 && Configuration::get('APLAZAME_BUTTON') === '#aplazame_payment_button') {
        Configuration::updateValue('APLAZAME_BUTTON', "div.payment-option:has(input[data-module-name='{$module->name}'])");
    }

    $buttonImage = Configuration::get('APLAZAME_BUTTON_IMAGE');
    Configuration::updateValue('APLAZAME_BUTTON_IMAGE', 'https://aplazame.com/static/img/buttons/' . $buttonImage . '.png');

    return true;
}

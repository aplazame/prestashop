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

function upgrade_module_6_0_0(Aplazame $module)
{
    Configuration::updateValue('APLAZAME_BUTTON_INSTALMENTS', Configuration::get('APLAZAME_BUTTON'));
    Configuration::deleteByName('APLAZAME_BUTTON');

    if (_PS_VERSION_ >= 1.7) {
        Configuration::updateValue('APLAZAME_BUTTON_IMAGE', '');
        Configuration::updateValue('APLAZAME_BUTTON_IMAGE_PAY_LATER', '');
        Configuration::updateValue('APLAZAME_BUTTON_PAY_LATER', "div.payment-option:has(input[data-module-name='{$module->name}_pay_later'])");
    } else {
        Configuration::updateValue('APLAZAME_BUTTON_IMAGE_PAY_LATER', 'https://aplazame.com/static/img/buttons/pay-later-227x46.png');
        Configuration::updateValue('APLAZAME_BUTTON_PAY_LATER', '#aplazame_pay_later_payment_button');
    }

    Configuration::updateValue('APLAZAME_PAYMENT_INSTALMENTS', true);
    Configuration::updateValue('APLAZAME_PAYMENT_PAY_LATER', false);

    $module->registerHook('actionOrderStatusUpdate');

    return true;
}

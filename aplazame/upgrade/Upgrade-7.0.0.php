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

function upgrade_module_7_0_0(Aplazame $module)
{
    Configuration::updateValue('APLAZAME_BUTTON_TITLE', $module->l('Pay with Aplazame'));
    Configuration::updateValue('APLAZAME_BUTTON_DESCRIPTION',
        'Compra primero y paga después con <a href="https://aplazame.com" target="_blank">Aplazame</a>.', true);

    Configuration::updateValue('APLAZAME_BUTTON', Configuration::get('APLAZAME_BUTTON_INSTALMENTS'));
    Configuration::deleteByName('APLAZAME_BUTTON_INSTALMENTS');
    Configuration::deleteByName('APLAZAME_BUTTON_PAY_LATER');
    Configuration::deleteByName('APLAZAME_BUTTON_IMAGE_PAY_LATER');
    Configuration::deleteByName('APLAZAME_PAYMENT_INSTALMENTS');
    Configuration::deleteByName('APLAZAME_PAYMENT_PAY_LATER');

    if ((_PS_VERSION_ < 1.7) && (Configuration::get('APLAZAME_BUTTON_IMAGE') == 'https://aplazame.com/static/img/buttons/white-148x46.png')) {
        Configuration::updateValue('APLAZAME_BUTTON_IMAGE', 'https://cdn.aplazame.com/static/img/buttons/aplazame-blended-button-227px.png');
    }

    return true;
}

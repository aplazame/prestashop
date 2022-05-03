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

function upgrade_module_4_0_0(Aplazame $module)
{
    if (!$module->installOrderState()) {
        return false;
    }

    try {
        $module->updateSettingsFromAplazame(Configuration::get('APLAZAME_SECRET_KEY'));
    } catch (Aplazame_Sdk_Api_ApiClientException $apiClientException) {
        return false;
    }

    return true;
}

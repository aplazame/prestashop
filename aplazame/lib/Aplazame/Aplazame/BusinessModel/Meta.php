<?php
/**
 * This file is part of the official Aplazame module for PrestaShop.
 *
 * @author    Aplazame <soporte@aplazame.com>
 * @copyright 2015-2019 Aplazame
 * @license   see file: LICENSE
 */

/**
 * Meta.
 */
class Aplazame_Aplazame_BusinessModel_Meta
{
    public static function create()
    {
        /** @var Aplazame $aplazame */
        $aplazame = ModuleCore::getInstanceByName('aplazame');

        $aMeta = new self();
        $aMeta->module = array(
                'name' => 'aplazame:prestashop',
                'version' => $aplazame->version,
            );
        $aMeta->version = _PS_VERSION_;

        return $aMeta;
    }
}

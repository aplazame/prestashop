<?php
/**
 * This file is part of the official Aplazame module for PrestaShop.
 *
 * @author    Aplazame <soporte@aplazame.com>
 * @copyright 2015-2024 Aplazame
 * @license   see file: LICENSE
 */

final class AdminAplazameApiProxyController extends ModuleAdminController
{
    /**
     * @var Aplazame
     */
    private $aplazame;

    public function __construct()
    {
        parent::__construct();

        $this->aplazame = ModuleCore::getInstanceByName('aplazame');
    }

    public function postProcess()
    {
        $method = Tools::getValue('method');
        $path = Tools::getValue('path');
        $data = Tools::getValue('data');
        $data = json_decode($data);
        if (!$data) {
            $data = array();
        }

        $response = $this->aplazame->callToRest($method, $path, $data);

        die(json_encode($response));
    }
}

<?php


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
        if (!in_array($method, array('GET', 'POST', 'PUT', 'PATCH', 'DELETE'))) {
            return parent::postProcess();
        }

        $path = Tools::getValue('path');
        $data = Tools::getValue('data');
        $data = json_decode($data);
        if (!$data) {
            $data = array();
        }

        $result = $this->aplazame->callToRest($method, $path, $data, true);

        die(trim($result['response']));
    }
}

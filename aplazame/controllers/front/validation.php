<?php

class AplazameValidationModuleFrontController extends ModuleFrontController {

    public function postProcess() {

        if ($this->module->active == false)
            die;

        return $this->module->validateController(Tools::getValue('order_id'));
    }

    protected function isValidOrder($code) {
        if ($code == '200') {
            return true;
        } else {
            return false;
        }
    }

}

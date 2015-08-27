<?php

class AplazameValidationModuleFrontController extends ModuleFrontController {

    public function postProcess() {

        if ($this->module->active == false)
            die;

        if ($this->module->validateController(Tools::getValue('order_id'))){
            exit('success');
        } else {
            throw new Exception('Error processing order. We cannot validate the order.', 500);
        }
    }

    protected function isValidOrder($code) {
        if ($code == '200') {
            return true;
        } else {
            return false;
        }
    }

}

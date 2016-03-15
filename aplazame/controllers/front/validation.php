<?php

class AplazameValidationModuleFrontController extends ModuleFrontController
{
    public function postProcess()
    {
        if ($this->module->active == false) {
            die;
        }

        if ($this->module->validateController(Tools::getValue('checkout_token'))) {
            exit('success');
        } else {
            throw new Exception('Error processing order. We cannot validate the order. Maybe this is due to another order was created, a problem connecting with the webservice, no checkout token provided, or a server problem. Please contact the merchant to get all the data about this request.', 400);
        }
    }

    protected function isValidOrder($code)
    {
        if ($code == '200') {
            return true;
        } else {
            return false;
        }
    }
}

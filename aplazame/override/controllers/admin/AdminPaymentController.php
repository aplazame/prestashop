<?php

class AdminPaymentController extends AdminPaymentControllerCore
{
    public function renderModulesList()
    {
        //We do this to override PrestaShop only show partners module on AdminPayment on PS 1.6
        $this->filter_modules_list[] = 'aplazame';

        return parent::renderModulesList();
    }
}

<?php

class AdminProductsController extends AdminProductsControllerCore
{
    public function __construct()
    {
        parent::__construct();

        if (!Module::isInstalled('aplazame') || !Module::isEnabled('aplazame')) {
            return;
        }

        $aplazame = Module::getInstanceByName('aplazame');

        $this->bulk_actions['manageProductsAssociatedToAplazameCampaigns'] = array(
            'text' => $aplazame->l('Assign to / remove from Aplazame Campaigns'),
            'icon' => 'icon-check',
        );
    }
}

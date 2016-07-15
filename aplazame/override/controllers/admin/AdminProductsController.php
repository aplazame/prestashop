<?php

class AdminProductsController extends AdminProductsControllerCore
{
    public function __construct()
    {
        parent::__construct();

        if (!Module::isInstalled('aplazame') || !Module::isEnabled('aplazame')) {
            return;
        }

        $this->bulk_actions['assignProductsToAplazameCampaigns'] = array(
            'text' => $this->l('Assign to Aplazame Campaigns'),
            'icon' => 'icon-check',
        );
        $this->bulk_actions['removeProductsFromAplazameCampaigns'] = array(
            'text' => $this->l('Remove from Aplazame Campaigns'),
            'icon' => 'icon-remove',
        );
    }
}

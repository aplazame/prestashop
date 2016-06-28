<?php

class AdminProductsController extends AdminProductsControllerCore
{

    public function __construct()
    {
        parent::__construct();

        if (!Module::isInstalled('aplazame') || !Module::isEnabled('aplazame')) {
            return;
        }

        $this->bulk_actions['updateAplazameCampaign'] = array('text' => $this->l('Change products associated with Aplazame Campaign'), 'icon' => 'icon-refresh');
    }

    protected function processBulkUpdateAplazameCampaign()
    {
        if(Module::isInstalled('aplazame') && Module::isEnabled('aplazame')){
            /** @var Aplazame $aplazame */
            $aplazame = Module::getInstanceByName('aplazame');
            $campaign = Tools::getValue('APLAZAME_PRODUCT_CAMPAIGN','-1');
            if (Tools::isSubmit('submitUpdateAplazameCampaign')) {
                if ($this->tabAccess['edit'] !== '1') {
                    $this->errors[] = Tools::displayError('You do not have permission to edit this.');
                } else {
                    if (is_array($this->boxes) && !empty($this->boxes)) {
                        $products = Tools::getValue($this->table . 'Box');
                        if (is_array($products) && ($count = count($products))) {
                            if (intval(ini_get('max_execution_time')) < round($count * 1.5))
                                ini_set('max_execution_time', round($count * 1.5));

                            $selected_campaigns = Configuration::get('APLAZAME_SEL_CAMP', null);
                            $selected_campaigns = json_decode($selected_campaigns,true);
                            foreach ($products as $id_product) {
                                if(isset($selected_campaigns[$id_product])){
                                    $selected = $selected_campaigns[$id_product];
                                    $aplazame->deleteCampaignProduct($id_product, $selected);
                                }
                                $selected_campaigns[$id_product] = $campaign;
                            }
                            Configuration::updateValue('APLAZAME_SEL_CAMP', json_encode($selected_campaigns),false);
                            if($campaign != '-1'){
                                $aplazame->assignCampaignProducts($products,$campaign);
                            }
                            $this->redirect_after = self::$currentIndex . '&conf=4&token=' . $this->token;
                        } else {
                            $this->errors[] = Tools::displayError('You must select at least one element.');
                        }
                    }
                }
            }
        }
    }

}

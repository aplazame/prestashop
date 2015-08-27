<?php

class AplazameHistoryModuleFrontController extends ModuleFrontController
{
    var $limit_orders = 10;
        
    public function postProcess(){
        $auth = $this->getHeaderAuthorization();
        
        if (!$auth || $auth!=Configuration::get('APLAZAME_SECRET_KEY', null)){
            $this->apiResponse(array('error'=>'Authorization not valid'));
        }
        
        if (Tools::getValue('checkout_token',false) == false){
            $this->apiResponse(array('error'=>'mid not found as parameter checkout_token'));
        }

        if ($id_order = Order::getOrderByCartId(Tools::getValue('checkout_token'))){
            $Order = new Order($id_order);

            if ($Order->module == $this->module->name){
                $Customer = new Customer($Order->id_customer);
                $this->apiResponse($this->module->getCustomerHistory($Customer,$this->limit_orders));
            } else {
                $this->apiResponse(array('error'=>'mid is not from an Aplazame order'));
            }
        } else {
            $this->apiResponse(array('error'=>'no order exists with this mid'));
        }
    }
        
    public function apiResponse($data){
        exit(json_encode($data));
    }
    
    public function getHeaderAuthorization(){
        if (!function_exists('getallheaders')) 
        { 
            function getallheaders() 
            { 
                $headers = ''; 
               foreach ($_SERVER as $name => $value) 
               { 
                   if (substr($name, 0, 5) == 'HTTP_') 
                   { 
                       $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value; 
                   } 
               } 
               return $headers; 
            } 
        }
            
        $headers = getallheaders();

        if (isset($headers['Authorization']) && !empty($headers['Authorization'])){
            return trim(str_replace('Bearer', '', $headers['Authorization']));
        }
        return false;
        
    }
}

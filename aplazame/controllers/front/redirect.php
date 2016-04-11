<?php

class AplazameRedirectModuleFrontController extends ModuleFrontController
{
    /**
     * Do whatever you have to before redirecting the customer on the website of your payment processor.
     */
    public function postProcess()
    {
        /**
         * Oops, an error occurred.
         */
        if (Tools::getValue('action') == 'error'){
            
            if($cart_id = Tools::getValue('order_id',false)){
                
                if($this->module->validateController($cart_id,true,'Order cancelled by cancel_url')){
                    $this->module->duplicateCart($cart_id);
                    //It's an ajax call maded by aplazame JS, not return nothing
                    exit();
                }  
            }
            return $this->displayError('An error occurred while trying to redirect the customer');
        }
        else
        {
            //First solution to know if refreshed page: http://stackoverflow.com/a/6127748
            $refreshButtonPressed = isset($_SERVER['HTTP_CACHE_CONTROL']) &&
                    $_SERVER['HTTP_CACHE_CONTROL'] === 'max-age=0';

            $result = $this->module->callToRest('GET', '/orders?mid=' . Context::getContext()->cart->id, null, false);
            $result['response'] = json_decode($result['response'], true);

            if ($result['code'] == '200' && isset($result['response']['results'][0]['id']) && !$refreshButtonPressed) {
                //The cart exists on Aplazame, we try to send with another ID
                $this->module->duplicateCart();
            }
            $this->context->smarty->assign(array(
                    'cart_id' => Context::getContext()->cart->id,
                    'secure_key' => Context::getContext()->customer->secure_key,
                    'aplazame_public_key' => Configuration::get('APLAZAME_PUBLIC_KEY', null),
                    'aplazame_order_json' => json_encode($this->module->getCheckoutSerializer(0, Context::getContext()->cart->id)),
                    'aplazame_version' => ConfigurationCore::get('APLAZAME_API_VERSION', null),
                    'aplazame_host' => Configuration::get('APLAZAME_HOST', null),
                    'aplazame_is_sandbox' => Configuration::get('APLAZAME_SANDBOX', null)?'true':'false',
            ));
            return $this->setTemplate('redirect.tpl');
        }
    }

    protected function displayError($message, $description = false)
    {
        /**
         * Create the breadcrumb for your ModuleFrontController.
         */
        $this->context->smarty->assign('path', '
            <a href="'.$this->context->link->getPageLink('order', null, null, 'step=3').'">'.$this->module->l('Payment').'</a>
            <span class="navigation-pipe">&gt;</span>'.$this->module->l('Error'));

        /**
         * Set error message and description for the template.
         */
        array_push($this->errors, $this->module->l($message), $description);

        return $this->setTemplate('error.tpl');
    }
}

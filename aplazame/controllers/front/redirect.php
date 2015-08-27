<?php

class AplazameRedirectModuleFrontController extends ModuleFrontController
{
	/**
	 * Do whatever you have to before redirecting the customer on the website of your payment processor.
	 */
	public function postProcess()
	{
		/**
		 * Oops, an error occured.
		 */
		if (Tools::getValue('action') == 'error')
			return $this->displayError('An error occurred while trying to redirect the customer');
		else
		{
                    //First solution to know if refreshed page: http://stackoverflow.com/a/6127748
                    $refreshButtonPressed = isset($_SERVER['HTTP_CACHE_CONTROL']) && 
                            $_SERVER['HTTP_CACHE_CONTROL'] === 'max-age=0';
                    
                    $result = $this->module->callToRest('GET', '/orders?mid=' . Context::getContext()->cart->id, null, false);
                    $result['response'] = json_decode($result['response'], true);
                    if ($result['code'] == '200' && isset($result['response']['results'][0]['id']) && !$refreshButtonPressed) {
                        //The cart exists on Aplazame, we try to send with another ID
                        $oldCart = new Cart(Context::getContext()->cart->id);
                        $data = $oldCart->duplicate();
                        if($data['success']){
                            $cart = $data['cart'];
                            Context::getContext()->cart = $cart;
                            CartRule::autoAddToCart(Context::getContext());
                            Context::getContext()->cookie->id_cart = $cart->id;
                        }else{
                            $this->module->logError('Error: Cannot duplicate cart '.Context::getContext()->cart->id);
                        }
                    }
                    $this->context->smarty->assign(array(
                            'cart_id' => Context::getContext()->cart->id,
                            'secure_key' => Context::getContext()->customer->secure_key,
                            'aplazame_public_key' => Configuration::get('APLAZAME_PUBLIC_KEY', null),
                            'aplazame_order_json' => json_encode($this->module->getCheckoutSerializer(0,Context::getContext()->cart->id), 128),
                            'aplazame_version' => ConfigurationCore::get('APLAZAME_API_VERSION', null),
                            'aplazame_host' => Configuration::get('APLAZAME_HOST', null),
                            'aplazame_mode' => Configuration::get('APLAZAME_LIVE_MODE', null)?'false':'true',
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

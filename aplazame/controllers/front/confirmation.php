<?php

class AplazameConfirmationModuleFrontController extends ModuleFrontController
{
    public function postProcess()
    {
        if ((Tools::isSubmit('cart_id') == false) || (Tools::isSubmit('secure_key') == false))
            return false;

        $cart_id = Tools::getValue('cart_id');
        $secure_key = Tools::getValue('secure_key');

        $cart = new Cart((int)$cart_id);
        $customer = new Customer((int)$cart->id_customer);

        $payment_status = Configuration::get('PS_OS_PAYMENT'); // Default value for a payment that succeed.
        $message = null; // You can add a comment directly into the order so the merchant will see it in the BO.

        $module_name = $this->module->displayName;
        $currency_id = (int)Context::getContext()->currency->id;

        $order_id = Order::getOrderByCartId((int)$cart->id);

        if ($order_id && ($secure_key == $customer->secure_key))
        {
            $Order = new Order($order_id);
            if($Order->current_state == Configuration::get('PS_OS_ERROR') || $Order->current_state == Configuration::get('PS_OS_CANCELED')){
                 $this->errors[] = $this->module->l('An error occurred. Your order has not been confirmed by Aplazame or is canceled. Please contact the merchant to have more information.');
            }else{
                $module_id = $this->module->id;
                Tools::redirect('index.php?controller=order-confirmation&id_cart='.$cart_id.'&id_module='.$module_id.'&id_order='.$order_id.'&key='.$secure_key);
            }
        }
        else
        {
            if ($order_id) {
                $this->errors[] = $this->module->l('An error occurred but don\'t worry. Your order has been placed before. Please contact the merchant to have more informations or visit "My Account" to see your order history');
            } elseif($cart_id) {
                //We will try a last client side validation
                if($this->module->validateController($cart_id)){
                    $order_id = Order::getOrderByCartId((int)$cart->id);
                    $module_id = $this->module->id;
                    Tools::redirect('index.php?controller=order-confirmation&id_cart='.$cart_id.'&id_module='.$module_id.'&id_order='.$order_id.'&key='.$secure_key);
                } else {
                    $this->module->duplicateCart($cart_id);
                    $this->errors[] = $this->module->l('An error occurred. Your order has not been confirmed by Aplazame. Please contact the merchant to have more information.');
                }
            } else {
                $this->errors[] = $this->module->l('An error occurred. Please contact the merchant to have more information.');
            }
    
            
        }
        return $this->setTemplate('error.tpl');
    }
}

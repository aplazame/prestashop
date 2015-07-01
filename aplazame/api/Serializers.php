<?php

class Aplazame_Serializers 
{
    var $to_refund = 0;
    var $to_refund_tax = 0;
        
    private static function formatDecimals($price)
    {
        return Aplazame::formatDecimals($price);
    }

    private static function _getMetadata()
    {
        return array(
            "prestashop" => array(
                "version" => _PS_VERSION_
            ),
            "client" => "aplazame:prestashop",
            "version" => Aplazame::_version
        );
    }

    private function _orderTotal(Order $order)
    {
        return $order->getTotalPaid() - $this->to_refund;
    }

    protected function _getAddr(Address $address)
    {
        return array(
            "first_name"=> $address->firstname,
            "last_name"=> $address->lastname,
            "phone"=> $address->phone,
            "alt_phone"=> $address->phone_mobile,
            "street" => $address->address1,
            "address_addition" => $address->address2,
            "city" => $address->city,
            "state" => State::getNameById($address->id_state),
            "country" => CountryCore::getIsoById($address->id_country),
            "zip" => $address->postcode);
    }

    protected function getCustomer(Customer $customer)
    {
        
        $customer_serializer = array("gender"=>0);

        if (Validate::isLoadedObject($customer)) {
            $customer_serializer = array_merge($customer_serializer, array(
                "id"=>$customer->id,
                "type"=>"e",
                "email"=>$customer->email,
                "first_name"=>$customer->firstname,
                "last_name"=>$customer->lastname,
                "date_joined"=>date(DATE_ISO8601, strtotime($customer->date_add)),
                "last_login"=>date(DATE_ISO8601, strtotime($customer->date_upd))));
        } else {
            $customer_serializer = array_merge($customer_serializer, array(
                "type"=>"g",
                "email"=>Context::getContext()->customer->email,
                "first_name"=>Context::getContext()->customer->firstname,
                "last_name"=>Context::getContext()->customer->lastname));
        }

        return $customer_serializer;
    }

    protected function getShipping(Order $order,$cart = false)
    {
        
        if($cart){
            $id_address_delivery = $cart->id_address_delivery;
            $carrier = new Carrier($cart->id_carrier);
            $carrierName = $carrier->name;
            $shippingCost = $cart->getOrderTotal(true,Cart::ONLY_SHIPPING);
            $shippingTaxAmount = $cart->getOrderTotal(false,Cart::ONLY_SHIPPING) - $cart->getOrderTotal(true,Cart::ONLY_SHIPPING);
        }else{
            $id_address_delivery = $order->id_address_delivery;
            $carrier = $order->getShipping();
            $carrierName = $carrier['0']['carrier_name'];
            $shippingTaxAmount = $order->total_shipping_tax_incl - $order->total_shipping_tax_excl;
            $shippingCost = $order->total_shipping_tax_incl;
        }
        
        if(empty($carrierName)){
            $carrierName = 'Unknowed';
        }
        $shipping = null;
        $shipping_address = new Address($id_address_delivery);
        
        if ($shipping_address)
        {
            $shipping = array_merge($this->_getAddr($shipping_address), array(
                "price"=> static::formatDecimals($shippingCost),
                "name"=> $carrierName
            ));
        }

        return $shipping;
    }

    protected function getArticles(Order $order,$cart=false)
    {
        $this->to_refund = 0;
        $this->to_refund_tax = 0;
        if($cart){
            $products = $cart->getProducts();
        }else{
            $products = $order->getProducts();
            foreach($products as $key => &$order_item){
                $order_item['product_quantity'] -= $order_item['product_quantity_refunded'];
                $this->to_refund += ($order_item['product_quantity_refunded'] * $order_item['unit_price_tax_incl']);
                $this->to_refund_tax += ($order_item['product_quantity_refunded'] * $order_item['unit_price_tax_excl']);
                if((int)$order_item['product_quantity'] <= 0 ){
                    unset($products[$key]);
                }
            }
        }
		
		
        $articles = array();
        $link = new Link();
        foreach($products as $order_item)
        {
            if($cart){
                
                $productId = $order_item['id_product'];
                $Product = new Product($productId);
                $discounts = $order_item['reduction_applies'];
                $quantity = $order_item['cart_quantity'];
                $price = $order_item['price_wt'];
                $description_short = strip_tags($order_item['description_short']);
                $image_url = str_replace('http://', '', $link->getImageLink('product', $order_item['id_image']));
                $name = $order_item['name'];
                $sku = $order_item['id_product_attribute'];
                $product_url = $link->getProductLink($productId);
            }else{
                $productId = $order_item['product_id'];
                $Product = new Product($productId);
                $discounts = $order_item['reduction_amount_tax_incl'];
                $quantity = $order_item['product_quantity'];
                $price = $order_item['unit_price_tax_incl'];
                $description_short = strip_tags($order_item['description_short']);
                $image_url = str_replace('http://', '', $link->getImageLink('product', $order_item['image']->id));
                $name = $order_item['product_name'];
                $sku = $order_item['product_attribute_id'];
                $product_url = $link->getProductLink($productId);
            }

            $articles[] = array(
                "id" => $productId,
                "sku" => $sku,
                "name" => $name,
                "description" => substr($description_short, 0, 255),
                "url" =>$product_url,
                "image_url" => 'http://'.$image_url,
                "quantity" => intval($quantity),
                "price" => static::formatDecimals($price),
                "tax_rate" => static::formatDecimals($Product->getTaxesRate()),
                "discount" => static::formatDecimals($discounts));
        }

        return $articles;
    }

    protected function getRenderOrder(Order $order,$cart=false)
    {
        $articles = $this->getArticles($order, $cart);
        if($cart){
            $id_order = $cart->id;
            $id_currency = $cart->id_currency;
            $total_amount = $cart->getOrderTotal(true);
            $tax_amount = $total_amount - $cart->getOrderTotal(false);
            $discounts = $cart->getOrderTotal(true, Cart::ONLY_DISCOUNTS);
        }else{
            $id_order = $order->id_cart;
            $id_currency = $order->id_currency;
            $total_amount = $this->_orderTotal($order);
            $tax_amount = $total_amount - ($order->total_paid_tax_excl - $this->to_refund_tax);
            $discounts = $order->total_discounts;
        }
        
        $Currency = new Currency($id_currency);
        $currency = $Currency->iso_code;
        
        return array(
            "id"=>$id_order,
            "articles"=>$articles,
            "currency"=>$currency,
            "tax_amount"=>static::formatDecimals($tax_amount),
            "total_amount"=>static::formatDecimals($total_amount),
            "discount"=>static::formatDecimals($discounts));
    }

    public function getHistory(Customer $customer,$limit)
    {
        $history_collection = Db::getInstance()->executeS('SELECT * FROM '._DB_PREFIX_.'orders '
                . ' WHERE id_customer = '.$customer->id.' '
                . ' ORDER BY id_order DESC LIMIT '.$limit);

        $history = array();
        foreach($history_collection as $order_history){
            $Order = new Order($order_history['id_order']);
            $Currency = new Currency($Order->id_currency);
            $currency = $Currency->iso_code;
            $BillingAddress = new Address($Order->id_address_invoice);
            $status = $Order->getCurrentStateFull(Context::getContext()->language->id);
            $history[] = array(
                "id"=>$Order->id_cart,
                "amount"=>static::formatDecimals($this->_orderTotal($Order)),
                "due"=> static::formatDecimals($this->_orderTotal($Order)),
                "status"=>$status['name'],
                "type"=>$Order->module,
                "order_date"=>date(DATE_ISO8601, strtotime($Order->date_add)),
                "currency"=>$currency,
                "billing"=>$this->_getAddr($BillingAddress),
                "shipping"=>$this->getShipping($Order));
        }

        return $history;
    }

    /*public function getOrderUpdate()
    {
        $order = $this->getOrder();

        return array(
            "order"=>$this->getRenderOrder(),
            "billing"=>$this->_getAddr($order->getBillingAddress()),
            "shipping"=>$this->getShipping($order),
            "meta"=>static::_getMetadata());
    }*/

    public function getCheckout(Order $order,$cart = false)
    {
        
        if($cart){
            $id_customer = $cart->id_customer;
            $id_billing_address = $cart->id_address_invoice;
            $id_cart = $cart->id;
            $secure_key = $cart->secure_key;
        }else{
            $id_customer = $order->id_customer;
            $id_billing_address = $order->id_address_invoice;
            $id_cart = $order->id;
            $secure_key = $order->secure_key;
        }
        
        $Customer = new Customer($id_customer);
        $BillingAddress = new Address($id_billing_address);
        
        if(_PS_VERSION_ < 1.6){
            $merchant = array(
            "public_api_key"=> Configuration::get('APLAZAME_PUBLIC_KEY', null),
            "confirmation_url"=>_PS_BASE_URL_.__PS_BASE_URI__.'index.php?fc=module&module=aplazame&controller=validation',
            "cancel_url"=>_PS_BASE_URL_.__PS_BASE_URI__.'index.php?fc=module&module=aplazame&controller=redirect&action=error',
            "checkout_url"=> _PS_BASE_URL_.__PS_BASE_URI__.'pedido-rapido',
            "success_url"=>_PS_BASE_URL_.__PS_BASE_URI__.'index.php?fc=module&module=aplazame&controller=confirmation&cart_id='.$id_cart.'&secure_key='.$secure_key);
        }else{
            $merchant = array(
            "public_api_key"=> Configuration::get('APLAZAME_PUBLIC_KEY', null),
            "confirmation_url"=>_PS_BASE_URL_.__PS_BASE_URI__.'module/aplazame/validation',
            "cancel_url"=>_PS_BASE_URL_.__PS_BASE_URI__.'module/aplazame/redirect?action=error',
            "checkout_url"=> _PS_BASE_URL_.__PS_BASE_URI__.'pedido-rapido',
            "success_url"=>_PS_BASE_URL_.__PS_BASE_URI__.'module/aplazame/confirmation?cart_id='.$id_cart.'&secure_key='.$secure_key);
        }
        

        return array(
            "toc"=>True,
            "merchant"=>$merchant,
            "customer"=>$this->getCustomer($Customer),
            "order"=>$this->getRenderOrder($order,$cart),
            "billing"=>$this->_getAddr($BillingAddress),
            "shipping"=>$this->getShipping($order,$cart),
            "meta"=>static::_getMetadata());

    }
}


<?php

class AplazameSerializers
{
    public static function formatDecimals($amount)
    {
        return (int) number_format($amount, 2, '', '');
    }

    public static function decodeDecimals($decimals)
    {
        return $decimals / 100;
    }

    public static function getAddress(Address $address)
    {
        return array(
            'first_name' => $address->firstname,
            'last_name' => $address->lastname,
            'phone' => $address->phone,
            'alt_phone' => $address->phone_mobile,
            'street' => $address->address1,
            'address_addition' => $address->address2,
            'city' => $address->city,
            'state' => State::getNameById($address->id_state),
            'country' => Country::getIsoById($address->id_country),
            'postcode' => $address->postcode,
        );
    }

    protected static function getCustomer(Customer $customer)
    {
        return array(
            'id' => $customer->id,
            'type' => 'e', // e = existing
            'gender' => 0, // 0 = unknown
            'email' => $customer->email,
            'first_name' => $customer->firstname,
            'last_name' => $customer->lastname,
            'date_joined' => date(DATE_ISO8601, strtotime($customer->date_add)),
            'last_login' => date(DATE_ISO8601, strtotime($customer->date_upd)),
        );
    }

    public static function checkoutShipping(Order $order = null, Cart $cart = null)
    {
        if ($cart) {
            $id_address_delivery = $cart->id_address_delivery;
            $carrier = new Carrier($cart->id_carrier);
            $carrierName = $carrier->name;
            $shippingCost = $cart->getOrderTotal(false, Cart::ONLY_SHIPPING);
        } else {
            $id_address_delivery = $order->id_address_delivery;
            $carrier = $order->getShipping();
            $carrierName = $carrier['0']['carrier_name'];
            $shippingCost = $order->total_shipping_tax_excl;
        }

        if (empty($carrierName)) {
            $carrierName = 'Unknown';
        }

        $shipping = array_merge(
            self::getAddress(new Address($id_address_delivery)),
            array(
                'price' => self::formatDecimals($shippingCost),
                'name' => $carrierName,
            )
        );

        return $shipping;
    }

    protected static function getArticles(array $products)
    {
        $articles = array();
        $link = new Link();

        foreach ($products as $productData) {
            $articles[] = array(
                'id' => $productData['id_product'],
                'sku' => $productData['id_product_attribute'],
                'name' => $productData['name'],
                'description' => Tools::substr(strip_tags($productData['description_short']), 0, 255),
                'url' => $link->getProductLink($productData['id_product']),
                'image_url' => $link->getImageLink('product', $productData['id_image']),
                'quantity' => (int) $productData['cart_quantity'],
                'price' => self::formatDecimals($productData['price']),
                'tax_rate' => self::formatDecimals($productData['rate']),
                'discount' => self::formatDecimals($productData['reduction_applies']),
            );
        }

        return $articles;
    }

    protected static function checkoutOrder(Cart $cart)
    {
        $currency = new Currency($cart->id_currency);

        return array(
            'id' => $cart->id,
            'articles' => self::getArticles($cart->getProducts()),
            'currency' => $currency->iso_code,
            'total_amount' => self::formatDecimals($cart->getOrderTotal(true)),
            'discount' => self::formatDecimals($cart->getOrderTotal(true, Cart::ONLY_DISCOUNTS)),
        );
    }

    public static function getHistory($orders)
    {
        $history = array();

        foreach ($orders as $orderData) {
            $order = new Order($orderData['id_order']);
            $currency = new Currency($order->id_currency);
            $status = $order->getCurrentStateFull(Context::getContext()->language->id);

            $history[] = array(
                'id' => $order->id_cart,
                'amount' => self::formatDecimals($order->getTotalPaid()),
                'due' => self::formatDecimals($order->getTotalPaid()),
                'status' => $status['name'],
                'type' => $order->module,
                'order_date' => date(DATE_ISO8601, strtotime($order->date_add)),
                'currency' => $currency->iso_code,
                'billing' => self::getAddress(new Address($order->id_address_invoice)),
                'shipping' => self::checkoutShipping($order),
            );
        }

        return $history;
    }

    public static function getCheckout(Cart $cart, $moduleId, $orderId)
    {
        /** @var Aplazame $aplazame */
        $aplazame = ModuleCore::getInstanceByName('aplazame');

        $link = Context::getContext()->link;
        $confirmationQuery = array(
            'id_cart' => $cart->id,
            'key' => $cart->secure_key,
        );
        $cancelQuery = array(
            'id_cart' => $cart->id,
            'key' => $cart->secure_key,
        );
        $successQuery = array(
            'id_cart' => $cart->id,
            'id_module' => $moduleId,
            'id_order' => $orderId,
            'key' => $cart->secure_key,
        );

        return array(
            'toc' => true,
            'merchant' => array(
                'confirmation_url' => $link->getModuleLink('aplazame', 'confirmation', $confirmationQuery),
                'cancel_url' => $link->getModuleLink('aplazame', 'cancel', $cancelQuery),
                'checkout_url' => $link->getPageLink('order'),
                'success_url' => $link->getPageLink('order-confirmation', null, null, $successQuery),
            ),
            'customer' => self::getCustomer(new Customer($cart->id_customer)),
            'order' => self::checkoutOrder($cart),
            'billing' => self::getAddress(new Address($cart->id_address_invoice)),
            'shipping' => self::checkoutShipping(null, $cart),
            'meta' => array(
                'module' => array(
                    'name' => 'aplazame:prestashop',
                    'version' => $aplazame->version,
                ),
                'version' => _PS_VERSION_,
            ),
        );
    }

    public static function getArticleCampaign(Product $product)
    {
        $link = new Link();

        return array(
            'id' => $product->id,
            'name' => $product->name,
            'description' => Tools::substr(strip_tags($product->description_short), 0, 255),
            'url' => $link->getProductLink($product),
            'image_url' => $link->getImageLink('product', $product->getCoverWs()),
        );
    }
}

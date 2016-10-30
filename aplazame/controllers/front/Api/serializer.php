<?php

final class AplazameApiSerializer
{
    /**
     * @param Product $product
     *
     * @return array
     */
    public static function article(Product $product)
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

    /**
     * @param Order $order
     *
     * @return array
     */
    public static function historicalOrder(Order $order)
    {
        $currency = new Currency($order->id_currency);
        $status = $order->getCurrentStateFull(Context::getContext()->language->id);

        $serialized = array(
            'id' => $order->id_cart,
            'amount' => AplazameSerializers::formatDecimals($order->getTotalPaid()),
            'due' => AplazameSerializers::formatDecimals($order->getTotalPaid()),
            'status' => $status['name'],
            'type' => $order->module,
            'order_date' => date(DATE_ISO8601, strtotime($order->date_add)),
            'currency' => $currency->iso_code,
            'billing' => AplazameSerializers::getAddress(new Address($order->id_address_invoice)),
            'shipping' => AplazameSerializers::checkoutShipping($order),
        );

        return $serialized;
    }
}

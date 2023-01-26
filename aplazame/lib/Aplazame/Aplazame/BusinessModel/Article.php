<?php
/**
 * This file is part of the official Aplazame module for PrestaShop.
 *
 * @author    Aplazame <soporte@aplazame.com>
 * @copyright 2015-2023 Aplazame
 * @license   see file: LICENSE
 */

/**
 * Article.
 */
class Aplazame_Aplazame_BusinessModel_Article
{
    public static function createFromProductData(Cart $cart, array $productData)
    {
        $link = Context::getContext()->link;
        $id = $productData['id_product'];
        $sku = isset($productData['id_product_attribute']) ? (int) $productData['id_product_attribute'] : null;

        // This is to consider the price when customer rules are applied.
        $discount = Product::getPriceStatic(
            $id,
            false,
            $sku,
            2,
            null,
            true,
            true,
            1,
            false,
            $cart->id_customer,
            $cart->id
        );

        // $productData['rate'] contains default tax rate of the product but ignores the custom tax rules for each state/province (for example: Canary Islands).
        // Instead, we use the following:
        $address_id = (int) $productData['id_address_delivery'];
        $ratio_tax = Tax::getProductTaxRate(
                (int) $productData['id_product'],
                (int) $address_id
            );

        $aArticle = new self();
        $aArticle->id = $id;
        $aArticle->sku = $sku;
        $aArticle->name = $productData['name'];
        $aArticle->url = $link->getProductLink($id);
        $aArticle->image_url = $link->getImageLink('product', $productData['id_image']);
        $aArticle->quantity = (int) $productData['cart_quantity'];
        $aArticle->price = Aplazame_Sdk_Serializer_Decimal::fromFloat($productData['price'] + $discount);
        $aArticle->tax_rate = Aplazame_Sdk_Serializer_Decimal::fromFloat($ratio_tax);
        $aArticle->discount = Aplazame_Sdk_Serializer_Decimal::fromFloat($discount);
        $aArticle->description = Tools::substr(strip_tags($productData['description_short']), 0, 255);

        return $aArticle;
    }
}

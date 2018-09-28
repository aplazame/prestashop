<?php
/**
 * This file is part of the official Aplazame module for PrestaShop.
 *
 * @author    Aplazame <soporte@aplazame.com>
 * @copyright 2015-2016 Aplazame
 * @license   see file: LICENSE
 */

/**
 * Article.
 */
class Aplazame_Aplazame_BusinessModel_Article
{
    public static function crateFromProductData(array $productData)
    {
        $link = Context::getContext()->link;
        $id = $productData['id_product'];
        $sku = isset($productData['id_product_attribute']) ? (int)$productData['id_product_attribute'] : null;
        $discount = Product::getPriceStatic(
            $id,
            false,
            $sku,
            2,
            null,
            true
        );

        $aArticle = new self();
        $aArticle->id = $id;
        $aArticle->sku = $sku;
        $aArticle->name = $productData['name'];
        $aArticle->url = $link->getProductLink($id);
        $aArticle->image_url = $link->getImageLink('product', $productData['id_image']);
        $aArticle->quantity = (int) $productData['cart_quantity'];
        $aArticle->price = Aplazame_Sdk_Serializer_Decimal::fromFloat($productData['price'] + $discount);
        $aArticle->tax_rate = Aplazame_Sdk_Serializer_Decimal::fromFloat($productData['rate']);
        $aArticle->discount = Aplazame_Sdk_Serializer_Decimal::fromFloat($discount);
        $aArticle->description = Tools::substr(strip_tags($productData['description_short']), 0, 255);

        return $aArticle;
    }
}

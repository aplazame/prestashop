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
        $discount = ($productData['price_without_reduction'] - $productData['price_with_reduction']) / (1 + $productData['rate'] / 100);

        $aArticle = new self();
        $aArticle->id = $productData['id_product'];
        $aArticle->sku = $productData['id_product_attribute'];
        $aArticle->name = $productData['name'];
        $aArticle->url = $link->getProductLink($productData['id_product']);
        $aArticle->image_url = $link->getImageLink('product', $productData['id_image']);
        $aArticle->quantity = (int) $productData['cart_quantity'];
        $aArticle->price = Aplazame_Sdk_Serializer_Decimal::fromFloat($productData['price'] + $discount);
        $aArticle->tax_rate = Aplazame_Sdk_Serializer_Decimal::fromFloat($productData['rate']);
        $aArticle->discount = Aplazame_Sdk_Serializer_Decimal::fromFloat($discount);
        $aArticle->description = Tools::substr(strip_tags($productData['description_short']), 0, 255);

        return $aArticle;
    }
}

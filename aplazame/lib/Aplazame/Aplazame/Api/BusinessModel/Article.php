<?php
/**
 * This file is part of the official Aplazame module for PrestaShop.
 *
 * @author    Aplazame <soporte@aplazame.com>
 * @copyright 2015-2019 Aplazame
 * @license   see file: LICENSE
 */

class Aplazame_Aplazame_Api_BusinessModel_Article
{
    public static function createFromProduct(Product $product)
    {
        $link = Context::getContext()->link;

        return array(
            'id' => $product->id,
            'name' => $product->name,
            'description' => Tools::substr(strip_tags($product->description_short), 0, 255),
            'url' => $link->getProductLink($product),
            'image_url' => $link->getImageLink('product', $product->getCoverWs()),
        );
    }
}

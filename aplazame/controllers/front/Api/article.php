<?php
/**
 * This file is part of the official Aplazame module for PrestaShop.
 *
 * @author    Aplazame <soporte@aplazame.com>
 * @copyright 2015-2020 Aplazame
 * @license   see file: LICENSE
 */

final class AplazameApiArticle
{
    /**
     * @var Db
     */
    private $db;

    public function __construct(Db $db)
    {
        $this->db = $db;
    }

    public function articles(array $queryArguments)
    {
        $page = (isset($queryArguments['page'])) ? (int) $queryArguments['page'] : 1;
        $page_size = (isset($queryArguments['page_size'])) ? (int) $queryArguments['page_size'] : 10;
        $offset = ($page - 1) * $page_size;

        $products = $this->db->executeS(
            'SELECT id_product FROM ' . _DB_PREFIX_ . 'product'
            . ' LIMIT ' . $offset . ', ' . $page_size
        );

        $articles = array();
        $lang_id = Context::getContext()->language->id;
        foreach ($products as $productData) {
            $product = new Product($productData['id_product'], false, $lang_id);
            $articles[] = Aplazame_Aplazame_Api_BusinessModel_Article::createFromProduct($product);
        }

        return AplazameApiModuleFrontController::collection($page, $page_size, $articles);
    }
}

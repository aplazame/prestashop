{*
 * This file is part of the official Aplazame module for PrestaShop.
 *
 * @author    Aplazame <soporte@aplazame.com>
 * @copyright 2015-2021 Aplazame
 * @license   see file: LICENSE
 *}

<div
    {if $aplazame_widget_legacy}
        data-aplazame-widget-instalments=""
        data-view="product"
    {else}
        data-aplazame-widget-instalments="v4"
		data-type="product"
		data-option-primary-color="{$aplazame_primary_color|escape:'htmlall':'UTF-8'}"
		data-option-layout="{$aplazame_layout|escape:'htmlall':'UTF-8'}"
        data-option-align="{$aplazame_align|escape:'htmlall':'UTF-8'}"
        data-option-border-product="{$aplazame_border|escape:'htmlall':'UTF-8'}"
    {/if}
    data-amount="{$aplazame_amount|escape:'htmlall':'UTF-8'}"
    data-currency="{$aplazame_currency_iso|escape:'htmlall':'UTF-8'}"
    {if $aplazame_css}
        data-price="{$aplazame_css|escape:'htmlall':'UTF-8'}"
    {/if}
    data-article-id="{$aplazame_article_id|escape:'htmlall':'UTF-8'}"
    data-option-legal-advice="{$aplazame_legal_advice|escape:'htmlall':'UTF-8'}"
    {if $aplazame_default_instalments}
        data-option-default-instalments="{$aplazame_default_instalments|escape:'htmlall':'UTF-8'}"
    {/if}
></div>

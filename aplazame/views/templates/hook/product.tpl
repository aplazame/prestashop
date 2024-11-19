{*
 * This file is part of the official Aplazame module for PrestaShop.
 *
 * @author    Aplazame <soporte@aplazame.com>
 * @copyright 2015-2024 Aplazame
 * @license   see file: LICENSE
 *}

<div
    {if $aplazame_widget_ver == 'v3'}
        data-aplazame-widget-instalments=""
        data-view="product"
    {elseif $aplazame_widget_ver == 'v4'}
        data-aplazame-widget-instalments="v4"
		data-type="product"
        data-option-max-amount-desired="{$aplazame_max_desired|escape:'htmlall':'UTF-8'}"
		data-option-primary-color="{$aplazame_primary_color|escape:'htmlall':'UTF-8'}"
		data-option-layout="{$aplazame_layout|escape:'htmlall':'UTF-8'}"
        data-option-align="{$aplazame_align|escape:'htmlall':'UTF-8'}"
        data-option-border-product="{$aplazame_border|escape:'htmlall':'UTF-8'}"
    {else}
        data-aplazame-widget-instalments="v5"
        data-type="product"
        data-option-slider="{$aplazame_slider|escape:'htmlall':'UTF-8'}"
        data-option-align="{$aplazame_align|escape:'htmlall':'UTF-8'}"
    {/if}
    data-amount="{$aplazame_amount|escape:'htmlall':'UTF-8'}"
    data-currency="{$aplazame_currency_iso|escape:'htmlall':'UTF-8'}"
    {if $aplazame_css}
        data-price="{$aplazame_css|escape:'htmlall':'UTF-8'}"
    {/if}
    data-article-id="{$aplazame_article_id|escape:'htmlall':'UTF-8'}"
    data-option-legal-advice="{$aplazame_legal_advice|escape:'htmlall':'UTF-8'}"
    data-option-downpayment-info="{$aplazame_downpayment_info|escape:'htmlall':'UTF-8'}"
    data-option-out-of-limits="{$aplazame_widget_out_of_limits|escape:'htmlall':'UTF-8'}"
    {if $aplazame_pay_in_4}
        data-pay-in-4=""
    {/if}
    {if $aplazame_default_instalments}
        data-option-default-instalments="{$aplazame_default_instalments|escape:'htmlall':'UTF-8'}"
    {/if}
    {if $aplazame_customer_id}
        data-option-customer-id="{$aplazame_customer_id|escape:'htmlall':'UTF-8'}"
    {/if}
>
    <div data-aplazame-loading></div>
</div>

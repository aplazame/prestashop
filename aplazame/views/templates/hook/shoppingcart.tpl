{*
 * This file is part of the official Aplazame module for PrestaShop.
 *
 * @author    Aplazame <soporte@aplazame.com>
 * @copyright 2015-2021 Aplazame
 * @license   see file: LICENSE
 *}

<div
    data-aplazame-widget-instalments=""
    data-view="cart"
    data-amount="{$aplazame_cart_total|escape:'htmlall':'UTF-8'}"
    data-currency="{$aplazame_currency_iso|escape:'htmlall':'UTF-8'}"
    data-price="#total_price"
    data-option-legal-advice="{$aplazame_legal_advice|escape:'htmlall':'UTF-8'}"
    {if $aplazame_default_instalments}
        data-option-default-instalments="{$aplazame_default_instalments|escape:'htmlall':'UTF-8'}"
    {/if}
></div>

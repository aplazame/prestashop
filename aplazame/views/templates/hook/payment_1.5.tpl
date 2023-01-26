{*
 * This file is part of the official Aplazame module for PrestaShop.
 *
 * @author    Aplazame <soporte@aplazame.com>
 * @copyright 2015-2023 Aplazame
 * @license   see file: LICENSE
 *}

<div class="row">
  <div class="col-xs-12 col-md-12">
    <p class="payment_module" id="aplazame_payment_button">
      <a href="{$link->getModuleLink('aplazame', 'redirect', array(), true)|escape:'htmlall':'UTF-8'}" title="{$aplazame.button_title}">
        {if $aplazame.button_image}
          <img src="{$aplazame.button_image|escape:'htmlall':'UTF-8'}" alt="{$aplazame.button_title}" />
        {/if}
        {$aplazame.button_title}
      </a>
    </p>
  </div>
</div>

<script>
  (window.aplazame = window.aplazame || []).push(function (aplazame) {
    aplazame.button({$aplazame.button|@json_encode})
  })
</script>

<style>
  #aplazame_payment_button a{
    padding-left: 17px;
  }
</style>

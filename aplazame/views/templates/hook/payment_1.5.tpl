{*
 * This file is part of the official Aplazame module for PrestaShop.
 *
 * @author    Aplazame <soporte@aplazame.com>
 * @copyright 2015-2021 Aplazame
 * @license   see file: LICENSE
 *}

{if $aplazame_instalments.is_enabled}
  <div class="row">
    <div class="col-xs-12 col-md-12">
      <p class="payment_module" id="aplazame_payment_button">
        <a href="{$link->getModuleLink('aplazame', 'redirect', $aplazame_instalments.button.product, true)|escape:'htmlall':'UTF-8'}" title="{l s='Flexible financing with Aplazame' mod='aplazame'}">
          {if $aplazame_instalments.button_image}
            <img src="{$aplazame_instalments.button_image|escape:'htmlall':'UTF-8'}" alt="{l s='Flexible financing with Aplazame' mod='aplazame'}" />
          {/if}
          {l s='Aplazame - Flexible financing' mod='aplazame'}
        </a>
      </p>
    </div>
  </div>

  <script>
    (window.aplazame = window.aplazame || []).push(function (aplazame) {
        aplazame.button({$aplazame_instalments.button|@json_encode})
    })
  </script>
{/if}

{if $aplazame_pay_later.is_enabled}
  <div class="row">
    <div class="col-xs-12 col-md-12">
      <p class="payment_module" id="aplazame_pay_later_payment_button">
        <a href="{$link->getModuleLink('aplazame', 'redirect', $aplazame_pay_later.button.product, true)|escape:'htmlall':'UTF-8'}" title="{l s='Pay in 15 days with Aplazame' mod='aplazame'}">
          {if $aplazame_pay_later.button_image}
            <img src="{$aplazame_pay_later.button_image|escape:'htmlall':'UTF-8'}" alt="{l s='Pay in 15 days with Aplazame' mod='aplazame'}" />
          {/if}
          {l s='Aplazame - Pay in 15 days' mod='aplazame'}
        </a>
      </p>
    </div>
  </div>

  <script>
    (window.aplazame = window.aplazame || []).push(function (aplazame) {
        aplazame.button({$aplazame_pay_later.button|@json_encode})
    })
  </script>
{/if}

<style>
  #aplazame_payment_button a, #aplazame_pay_later_payment_button a{
    padding-left: 17px;
  }
</style>

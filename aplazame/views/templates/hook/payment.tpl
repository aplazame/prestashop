<div class="row">
  <div class="col-xs-12 col-md-12">
    <p class="payment_module" id="aplazame_payment_button">
      <a href="{$link->getModuleLink('aplazame', 'redirect', array(), true)|escape:'htmlall':'UTF-8'}" title="{l s='Pay with Aplazame' mod='aplazame'}">
        <img src="{$aplazame_button_image_uri|escape:'htmlall':'UTF-8'}" alt="{l s='Pay with Aplazame' mod='aplazame'}" />
        {l s='Aplaza o fracciona tu compra con Aplazame' mod='aplazame'}
      </a>
    </p>
  </div>
</div>

<style>
  #aplazame_payment_button a{
    padding-left: 17px;
  }
</style>

<script>
aplazame.button({
  selector: "{$aplazame_button}",
  amount: {$aplazame_cart_total},
  currency: "{$aplazame_currency_iso}"
});
</script>

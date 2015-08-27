<div class="row">
  <div class="col-xs-12 col-md-12">
    <p class="payment_module" id="aplazame_payment_button">
      <a href="{$link->getModuleLink('aplazame', 'redirect', array(), true)|escape:'htmlall':'UTF-8'}" title="{l s='Pay with my payment module' mod='aplazame'}">
        <img src="https://aplazame.com/static/img/buttons/{$aplazame_button_image|escape:'htmlall':'UTF-8'}.png" alt="{l s='Pay with my payment module' mod='aplazame'}" />
        {l s='Aplaza o fracciona tu compra con ' mod='aplazame'}Aplazame
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
  id: "{$aplazame_button_id}",
  amount: {$aplazame_cart_total},
  currency: "{$aplazame_currency_iso}"
});
</script>

{*
 * This file is part of the official Aplazame module for PrestaShop.
 *
 * @author    Aplazame <soporte@aplazame.com>
 * @copyright 2015-2016 Aplazame
 * @license   see file: LICENSE
 *}

<script>
aplazame.button({
  selector: "{$aplazame_button|escape:'javascript':'UTF-8'}",
  amount: {$aplazame_cart_total|escape:'javascript':'UTF-8'},
  currency: "{$aplazame_currency_iso|escape:'javascript':'UTF-8'}"
});
</script>

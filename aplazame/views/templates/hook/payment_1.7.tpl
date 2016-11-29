{*
 * This file is part of the official Aplazame module for PrestaShop.
 *
 * @author    Aplazame <soporte@aplazame.com>
 * @copyright 2015-2016 Aplazame
 * @license   see file: LICENSE
 *}

<script>
aplazame.button({
  selector: "{$aplazame_button}",
  amount: {$aplazame_cart_total},
  currency: "{$aplazame_currency_iso}"
});
</script>

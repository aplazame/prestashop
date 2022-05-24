{*
 * This file is part of the official Aplazame module for PrestaShop.
 *
 * @author    Aplazame <soporte@aplazame.com>
 * @copyright 2015-2022 Aplazame
 * @license   see file: LICENSE
 *}

<div>
  <h3>{l s='Processing payment with Aplazame' mod='aplazame'}</h3>

  <script>
    (window.aplazame = window.aplazame || []).push(function (aplazame) {
      aplazame.checkout("{$aid|escape:'htmlall':'UTF-8'}")
    })
  </script>
</div>

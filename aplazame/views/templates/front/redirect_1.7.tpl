{*
 * This file is part of the official Aplazame module for PrestaShop.
 *
 * @author    Aplazame <soporte@aplazame.com>
 * @copyright 2015-2024 Aplazame
 * @license   see file: LICENSE
 *}

{extends file='page.tpl'}

{block name="page_content"}
  <div>
    <h3>{l s='Processing payment with Aplazame' mod='aplazame'}</h3>

    <script>
      (window.aplazame = window.aplazame || []).push(function (aplazame) {
        aplazame.checkout("{$aid|escape:'htmlall':'UTF-8'}")
      })
    </script>
  </div>
{/block}

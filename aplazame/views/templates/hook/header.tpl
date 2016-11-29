{*
 * This file is part of the official Aplazame module for PrestaShop.
 *
 * @author    Aplazame <soporte@aplazame.com>
 * @copyright 2015-2016 Aplazame
 * @license   see file: LICENSE
 *}

<script type="text/javascript" src="{$aplazame_js_uri|escape:'htmlall':'UTF-8'}"></script>

<script>
    aplazame.init({
        host: "{$aplazame_api_base_uri|escape:'javascript':'UTF-8'}",
        publicKey: "{$aplazame_public_key|escape:'javascript':'UTF-8'}",
        sandbox: "{if $aplazame_is_sandbox}true{else}false{/if}"
    });
</script>

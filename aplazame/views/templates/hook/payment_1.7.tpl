{*
 * This file is part of the official Aplazame module for PrestaShop.
 *
 * @author    Aplazame <soporte@aplazame.com>
 * @copyright 2015-2023 Aplazame
 * @license   see file: LICENSE
 *}

<p>{$aplazame_description nofilter}</p>

<script>
    (window.aplazame = window.aplazame || []).push(function (aplazame) {
        aplazame.button({$aplazame_button|@json_encode nofilter})
    })
</script>

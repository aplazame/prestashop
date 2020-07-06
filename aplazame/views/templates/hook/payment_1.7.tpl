{*
 * This file is part of the official Aplazame module for PrestaShop.
 *
 * @author    Aplazame <soporte@aplazame.com>
 * @copyright 2015-2020 Aplazame
 * @license   see file: LICENSE
 *}

{if $aplazame_button.product.type == 'instalments'}
    <p>Financia tu compra en segundos con <a href="https://aplazame.com" target="_blank">Aplazame</a>.
        Puedes dividir el pago en cuotas mensuales y obtener una respuesta instantánea a tu solicitud. Sin comisiones ocultas.</p>
{elseif $aplazame_button.product.type == 'pay_later'}
    <p>Prueba primero y paga después con <a href="https://aplazame.com" target="_blank">Aplazame</a>.
        Compra sin que el dinero salga de tu cuenta. Llévate todo lo que te guste y paga 15 días después de recibir tu compra sólo lo que te quedes.</p>
{/if}

<script>
    (window.aplazame = window.aplazame || []).push(function (aplazame) {
        aplazame.button({$aplazame_button|@json_encode nofilter})
    })
</script>

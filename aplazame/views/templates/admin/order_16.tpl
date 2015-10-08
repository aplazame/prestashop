<div class="row aplazame-order-customer">
    <div class="col-xs-12">
        <dl class="well list-detail">
			<img src="{$logo|escape:'htmlall':'UTF-8'}" alt="{l s='Aplazame' mod='aplazame'}" />
            <dt>{l s='Identificador Operación' mod='aplazame'}</dt>
            <dd>{$aplazame_data.uuid}</dd>
            <dt>{l s='Identificador MID' mod='aplazame'}</dt>
            <dd>{$aplazame_data.mid}</dd>
        </dl>
    </div>
</div>
<script>
$(document).ready(function(){
$('.col-lg-5 .panel .icon-user').parents('.panel').append($('.aplazame-order-customer'));
});
</script>


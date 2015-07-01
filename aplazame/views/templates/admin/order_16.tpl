<div class="row aplazame-order-customer">
    <div class="col-xs-12">
        <dl class="well list-detail">
			<img src="{$logo|escape:'htmlall':'UTF-8'}" alt="{l s='Aplazame' mod='aplazame'}" />
            <dt>{l s='Pago mensual' mod='aplazame'}</dt>
            <dd style="font-size: 28px;">{convertPrice price=$aplazame_data.total_month} / {l s='mes' mod='aplazame'}</dd>
            <dt>{l s='Mensualidades' mod='aplazame'}</dt>
            <dd class="text-muted"><i class="icon-calendar-o"></i> {$aplazame_data.instalments} {l s='mensualidades' mod='aplazame'}</dd>
            <dt>{l s='TAE' mod='aplazame'}</dt>
            <dd><span class="badge">{$aplazame_data.annual_equivalent} %</span></dd>
            <dt>{l s='Intereses' mod='aplazame'}</dt>
            <dd><span class="badge">{convertPrice price=$aplazame_data.total_interest_amount}</span></dd>
            <dt>{l s='Total a pagar' mod='aplazame'}</dt>
            <dd><span class="badge badge-success">{convertPrice price=$aplazame_data.total_to_pay}</span></dd>
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


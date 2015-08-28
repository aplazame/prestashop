{if (isset($status) == true) && ($status == 'ok')}
<h3>{l s='Your order on %s is complete.' sprintf=$shop_name mod='aplazame'}</h3>
<p>
    <br />{l s='Reference' mod='aplazame'} : <span class="reference"><strong>{$reference|escape:'html':'UTF-8'}</strong></span>
    <br /><br />{l s='An email has been sent with this information.' mod='aplazame'}
    <br /><br />{l s='If you have questions, comments or concerns, please contact our' mod='aplazame'} <a href="{$link->getPageLink('contact', true)|escape:'html':'UTF-8'}">{l s='expert customer support team.' mod='aplazame'}</a>
</p>
{else}
<h3>{l s='Your order on %s has not been accepted.' sprintf=$shop_name mod='aplazame'}</h3>
<p>
    <br />- {l s='Reference' mod='aplazame'} <span class="reference"> <strong>{$reference|escape:'html':'UTF-8'}</strong></span>
    <br /><br />{l s='Please, try to order again.' mod='aplazame'}
    <br /><br />{l s='If you have questions, comments or concerns, please contact our' mod='aplazame'} <a href="{$link->getPageLink('contact', true)|escape:'html':'UTF-8'}">{l s='expert customer support team.' mod='aplazame'}</a>
</p>
{/if}
<hr />
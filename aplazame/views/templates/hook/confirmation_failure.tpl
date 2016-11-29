{*
 * @copyright 2015-2016 Aplazame
 * @license   see file: LICENSE
 *}

<h3>{l s='Your order on %s has not been accepted.' sprintf=$shop_name mod='aplazame'}</h3>
<p>
    <br />
    {l s='Reference' mod='aplazame'} : <span class="reference"><strong>{$reference|escape:'html':'UTF-8'}</strong></span>
    <br /><br />
    {l s='Please, try to order again.' mod='aplazame'}
    <br /><br />
    {l s='For any questions or for further information, please contact our' mod='aplazame'} <a href="{$link->getPageLink('contact', true)|escape:'html'}">{l s='customer service department.' mod='aplazame'}</a>
</p>
<hr />

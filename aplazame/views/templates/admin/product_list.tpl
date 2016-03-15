{if isset($updateAplazameCampaign_mode) && $updateAplazameCampaign_mode}
    <div class="panel">
        <div class="panel-heading">
            {l s='Aplazame Campaign'}
        </div>
        <form action="{$REQUEST_URI}" method="post">
            <div class="radio">
                <label for="APLAZAME_PRODUCT_CAMPAIGN">
                    <select name="APLAZAME_PRODUCT_CAMPAIGN" id="APLAZAME_PRODUCT_CAMPAIGN">
                        <option value="-1">{l s='None campaign'}</option>
                        {foreach key=key item=item from=$aplazame_campaigns}
                            <option value="{$item.id}" {if $item.id == $selected_aplazame_campaign}selected="selected"{/if} >{$item.name}</option>    
                        {/foreach}
                    </select>
                </label>
            </div>
            {foreach $POST as $key => $value}
                {if is_array($value)}
                    {foreach $value as $val}
                        <input type="hidden" name="{$key|escape:'html':'UTF-8'}[]" value="{$val|escape:'html':'UTF-8'}" />
                    {/foreach}
                {elseif strtolower($key) != 'id_order_state'}
                    <input type="hidden" name="{$key|escape:'html':'UTF-8'}" value="{$value|escape:'html':'UTF-8'}" />

                {/if}
            {/foreach}
            <div class="panel-footer">
                <button type="submit" name="cancel" class="btn btn-default">
                    <i class="icon-remove"></i>
                    {l s='Cancel'}
                </button>
                <button type="submit" class="btn btn-default" name="submitUpdateAplazameCampaign">
                    <i class="icon-check"></i>
                    {l s='Save Products to Aplazame Campaign'}
                </button>
            </div>
        </form>
    </div>
{/if}
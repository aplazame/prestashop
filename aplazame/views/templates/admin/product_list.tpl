{if isset($updateAplazameCampaign_mode) && $updateAplazameCampaign_mode}

    {if $old_presta}
        <style type="text/css">
            .panel{
                position: relative;
                padding: 20px;
                margin-bottom: 20px;
                border: solid 1px #d3d8db;
                background-color: #fff;
                -webkit-border-radius: 5px;
                border-radius: 5px;
            }
            .panel-heading{
                font-family: "Ubuntu Condensed",Helvetica,Arial,sans-serif;
                font-weight: 400;
                font-size: 14px;
                text-overflow: ellipsis;
                white-space: nowrap;
                color: #555;
                height: 32px;
            }
        </style>
    {/if}
    <div class="panel">
        <div class="panel-heading">
            {l s='Aplazame Campaign'}
        </div>
        <form action="{$REQUEST_URI}" method="post">
            <select name="APLAZAME_PRODUCT_CAMPAIGN" id="APLAZAME_PRODUCT_CAMPAIGN">
                <option value="-1">{l s='None campaign'}</option>
                {foreach key=key item=item from=$aplazame_campaigns}
                    <option value="{$item.id}" {if $item.id == $selected_aplazame_campaign}selected="selected"{/if} >{$item.name}</option>    
                {/foreach}
            </select>
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
<div id="product-aplazame" class="panel product-tab">
    <input type="hidden" name="submitted_tabs[]" value="Aplazame" />
    <h3 class="tab"> <i class="icon-info"></i> {l s='Aplazame' mod='aplazame'}</h3>
    <div class="form-group">
        <div class="col-lg-1"><span class="pull-right">{*/NOT MULTISHOP ENABLED/include file="controllers/products/multishop/checkbox.tpl" field="visibility" type="default"*}</span></div>		
        <label class="control-label col-lg-2" for="APLAZAME_PRODUCT_CAMPAIGN">
            {l s='Aplazame Campaign'}
        </label>
        <div class="col-lg-3">
            <select name="APLAZAME_PRODUCT_CAMPAIGN" id="APLAZAME_PRODUCT_CAMPAIGN">
                <option value="-1">{l s='None campaign'}</option>
                {foreach key=key item=item from=$aplazame_campaigns}
                    <option value="{$item.id}" {if $item.id == $selected_aplazame_campaign}selected="selected"{/if} >{$item.name}</option>    
                {/foreach}
            </select>
        </div>
    </div>
    <div class="panel-footer">
        <a href="{$link->getAdminLink('AdminProducts')|escape:'html':'UTF-8'}{if isset($smarty.request.page) && $smarty.request.page > 1}&amp;submitFilterproduct={$smarty.request.page|intval}{/if}" class="btn btn-default"><i class="process-icon-cancel"></i> {l s='Cancel'}</a>
        <button type="submit" name="submitAddproduct" class="btn btn-default pull-right"><i class="process-icon-save"></i> {l s='Save'}</button>
        <button type="submit" name="submitAddproductAndStay" class="btn btn-default pull-right"><i class="process-icon-save"></i> {l s='Save and stay'}</button>
    </div>
</div>

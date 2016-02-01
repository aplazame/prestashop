<div id="product-aplazame" class="panel product-tab">
	<input type="hidden" name="submitted_tabs[]" value="Aplazame" />
	<h3 class="tab"> <i class="icon-info"></i> {l s='Aplazame' mod='aplazame'}</h3>
<input type='text' name='priceoverride' value='{if $price_override}{$price_override}{/if}'>


<div class="panel-footer">
		<a href="{$link->getAdminLink('AdminProducts')|escape:'html':'UTF-8'}{if isset($smarty.request.page) && $smarty.request.page > 1}&amp;submitFilterproduct={$smarty.request.page|intval}{/if}" class="btn btn-default"><i class="process-icon-cancel"></i> {l s='Cancel'}</a>
		<button type="submit" name="submitAddproduct" class="btn btn-default pull-right" disabled="disabled"><i class="process-icon-loading"></i> {l s='Save'}</button>
		<button type="submit" name="submitAddproductAndStay" class="btn btn-default pull-right" disabled="disabled"><i class="process-icon-loading"></i> {l s='Save and stay'}</button>
	</div>
</div>
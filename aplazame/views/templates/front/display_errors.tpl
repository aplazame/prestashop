{*
 * This file is part of the official Aplazame module for PrestaShop.
 *
 * @author    Aplazame <soporte@aplazame.com>
 * @copyright 2015-2023 Aplazame
 * @license   see file: LICENSE
 *}

{if isset($errors) && $errors}
	<div class="alert alert-danger">
		<p>{if $errors|@count > 1}{l s='There are %d errors' sprintf=$errors|@count mod='aplazame'}{else}{l s='There is one error' mod='aplazame'}{/if}</p>
		<ol>
		{foreach from=$errors key=k item=error}
			<li>{$error}</li>
		{/foreach}
		</ol>
		{if isset($smarty.server.HTTP_REFERER) && !strstr($request_uri, 'authentication') && preg_replace('#^https?://[^/]+/#', '/', $smarty.server.HTTP_REFERER) != $request_uri}
			<p class="lnk"><a class="alert-link" href="{$smarty.server.HTTP_REFERER|escape:'html':'UTF-8'|secureReferrer}" title="{l s='Back' mod='aplazame'}">&laquo; {l s='Back' mod='aplazame'}</a></p>
		{/if}
	</div>
{/if}

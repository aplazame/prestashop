<div class="row">
	<div class="col-xs-12 col-md-6">
		<p class="payment_module" id="aplazame_payment_button">
                    <a href="{$link->getModuleLink('aplazame', 'redirect', array(), true)|escape:'htmlall':'UTF-8'}" title="{l s='Pay with my payment module' mod='aplazame'}">
                            <img src="https://aplazame.com/static/img/buttons/{$aplazame_button_image|escape:'htmlall':'UTF-8'}.png" alt="{l s='Pay with my payment module' mod='aplazame'}" />
                            {l s='Aplaza o fracciona tu compra con ' mod='aplazame'}Aplazame
                    </a>
		</p>
	</div>
</div>
<script>
var Aplazame_Button_Version = "{$aplazame_version}";
var Aplazame_Button_Url = "{$aplazame_url}";
var Aplazame_Button_Token = "{$aplazame_public_key}";
var Aplazame_Button_Sandbox = "{$aplazame_mode}";
var Aplazame_Button_Id_Button = "{$aplazame_button_id}";
var Aplazame_Total_Fixed = "{$aplazame_cart_total}"
var Aplazame_Currency_Iso = "{$aplazame_currency_iso}"
{literal}
var aplazame={version: Aplazame_Button_Version,url:Aplazame_Button_Url+"/checkout/button",dch:function(c,a){var b=document.getElementById(a.id);if(!b)return null;a.parentNode&&(b=b.parentNode);b.style.display=c;"undefined"!=typeof a.descriptionId&&(document.getElementById(a.descriptionId).style.display=c);return b},cdp:function(c,a){var b=this;this.dch(c,a)||document.addEventListener("DOMContentLoaded",function(){b.dch(c,a)})},button:function(c){this.cdp("none",c);var a=new XMLHttpRequest,b=this.url+"?amount="+c.amount+"&currency="+c.currency;if("withCredentials"in a)a.open("GET",b,!0);else if("undefined"!=typeof XDomainRequest)a=new XDomainRequest,a.open("GET",b);else return;a.setRequestHeader("Accept","application/vnd.aplazame"+(c.sandbox?".sandbox-":"-")+this.version+"+json");a.setRequestHeader("Authorization","Bearer "+c.token);a.withCredentials=!0;var d=this;a.onload=function(){if(200==a.status)d.cdp("block",c);else{var b=document.getElementById(c.id);b&&("input"!=!b.tagName.toLowerCase()&&(b=b.getElementsByTagName("input")[0]),b.checked&&(b.checked=!1))}};a.send()}};
aplazame.button({
  id: Aplazame_Button_Id_Button,
  token: Aplazame_Button_Token,
  amount: Aplazame_Total_Fixed,
  currency: Aplazame_Currency_Iso,
  sandbox: Aplazame_Button_Sandbox
});
{/literal}
</script>
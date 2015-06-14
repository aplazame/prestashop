{if !$aplazame_enabled_cookies}
<script type="text/javascript">
var Aplazame_Version = "{$aplazame_version}";
var Aplazame_Url = "{$aplazame_url}";
var Aplazame_Token = "{$aplazame_public_key}";
var Aplazame_Sandbox = "{$aplazame_mode}";
{literal}
try{var rdw={version:Aplazame_Version,url:Aplazame_Url+"/rdw",uuid:"xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx".replace(/[xy]/g,function(d){var b=16*Math.random()|0;return("x"==d?b:b&3|8).toString(16)}),clks:0,run:function(d){var b=this,a=function(){document.body.addEventListener("click",function(a){b.clks+=1;b.trgt=a.target},!1)};document.body?a():document.addEventListener("DOMContentLoaded",a);window.onbeforeunload=function(){b.request(d,!0)};setInterval(function e(){b.request(d);return e}(),1E4)},request:function(d,b){var a=new XMLHttpRequest,c=["uuid="+this.uuid,"clks="+this.clks,"hstry="+window.history.length,"url="+encodeURIComponent(window.location.href),"clr_dpth="+screen.colorDepth,"pxl_dpth="+screen.pixelDepth,"scrn_x="+window.screenX,"scrn_y="+window.screenY,"scrn_wdth="+screen.width,"scrn_hght="+screen.height,"hstry="+window.history.length];"undefined"!==typeof document.referrer&&c.push("referer="+encodeURIComponent(document.referrer));b&&"undefined"!==typeof this.trgt&&"a"===this.trgt.tagName.toLowerCase()&&c.push("next="+this.trgt.href);"undefined"!==typeof navigator.plugins&&c.push("plgns="+navigator.plugins.length);"undefined"!==typeof navigator.mimeTypes&&c.push("mm_typs="+navigator.mimeTypes.length);c=this.url+"?"+c.join("&");if("withCredentials"in a)a.open("GET",c,!0);else if("undefined"!=typeof XDomainRequest)a=new XDomainRequest,a.open("GET",c);else return;a.setRequestHeader("Accept","application/vnd.aplazame"+(d.sandbox?".sandbox-":"-")+this.version+"+json");a.setRequestHeader("Authorization","Bearer "+d.token);a.withCredentials=!0;a.send()}}}catch(err){};
rdw.run({
  token: Aplazame_Token,
  sandbox: Aplazame_Sandbox
});
{/literal}
</script>
{/if}
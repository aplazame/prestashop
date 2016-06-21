<script type="text/javascript" src="{$aplazame_host}/static/aplazame.js"></script>

<script>
aplazame.init({
    publicKey: "{$aplazame_public_key}",
    sandbox: "{$aplazame_is_sandbox}",
    analytics: "{if $aplazame_enabled_cookies eq 1}true{else}false{/if}"
});
</script>

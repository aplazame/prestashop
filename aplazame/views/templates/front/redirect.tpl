<style>
  iframe#aplazame{
    top:0px;
  }
</style>
<div>
  <h3>{l s='Redirect to aplazame' mod='aplazame'}:</h3>

  <script>
    aplazame.checkout({$aplazame_order_json});
  </script>

  <iframe src="" style="position:fixed; top:0px; left:0px; bottom:0px; right:0px; width:100%; height:100%; border:none; margin:0; padding:0; overflow:hidden;">
      {l s='Tu navegador web no soporta IFrames. Por favor, actualiza el navegador o intenta usar otro más moderno.' mod='aplazame'}
  </iframe>
</div>

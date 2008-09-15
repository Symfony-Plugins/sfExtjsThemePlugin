<?php
  $moduleName = ucfirst(sfInflector::camelize($this->getModuleName()));
  $toolbarName = "List".$moduleName."ToolbarTop";
?>

[?php
// constructor
$configArr['parameters'] = 'c';
$configArr['source'] = "
    // top toolbar config
    this.ttbConfig = ".$sfExtjs2Plugin->asAnonymousClass($toolbar_top->config_array).";

    // combine <?php echo $toolbarName ?>Config with arguments
    Ext.app.sx.<?php echo $toolbarName ?>.superclass.constructor.call(this, Ext.apply(this.ttbConfig, c));
";
$toolbar_top->attributes['constructor'] = $sfExtjs2Plugin->asMethod($configArr);
?]
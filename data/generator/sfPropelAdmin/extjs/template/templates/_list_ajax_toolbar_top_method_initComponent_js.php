<?php
  $moduleName = ucfirst(sfInflector::camelize($this->getModuleName()));
  $toolbarName = "List".$moduleName."ToolbarTop";
?>

[?php
// initComponent
$configArr['source'] = "Ext.app.sx.<?php echo $toolbarName ?>.superclass.initComponent.apply(this, arguments);";
$toolbar_top->attributes['initComponent'] = $sfExtjs2Plugin->asMethod($configArr);
?]

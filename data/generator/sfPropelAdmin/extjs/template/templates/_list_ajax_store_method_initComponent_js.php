<?php
  $moduleName = ucfirst(sfInflector::camelize($this->getModuleName()));
  $panelName = "List".$moduleName."Store";
?>

[?php
// constructor
$configArr['source'] = "Ext.app.sx.<?php echo $panelName ?>.superclass.initComponent.apply(this, arguments);";
$store->attributes['initComponent'] = $sfExtjs2Plugin->asMethod($configArr);
?]

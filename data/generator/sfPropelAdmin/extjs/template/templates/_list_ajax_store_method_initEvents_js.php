<?php
  $moduleName = ucfirst(sfInflector::camelize($this->getModuleName()));
  $panelName = "List".$moduleName."Store";
?>

[?php
// constructor
$configArr['source'] = "Ext.app.sx.<?php echo $panelName ?>.superclass.initEvents.apply(this);";
$store->attributes['initEvents'] = $sfExtjs2Plugin->asMethod($configArr);
?]

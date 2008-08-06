<?php
  $moduleName = ucfirst(sfInflector::camelize($this->getModuleName()));
  $panelName = "List".$moduleName."Store";
?>

[?php
// constructor
$configArr['source'] = "Ext.app.sx.<?php echo $panelName ?>.superclass.constructor.call(this, Ext.apply(".$sfExtjs2Plugin->asAnonymousClass($store->config_array).", c));";
$configArr['parameters'] = 'c';
$store->attributes['constructor'] = $sfExtjs2Plugin->asMethod($configArr);
?]

<?php
  $moduleName = ucfirst(sfInflector::camelize($this->getModuleName()));
  $panelName = "List".$moduleName."FilterPanel";
?>

[?php
// constructor
$configArr['source'] = "Ext.app.sx.<?php echo $panelName ?>.superclass.constructor.call(this, Ext.apply(".$sfExtjs2Plugin->asAnonymousClass($filterpanel->config_array).", c));";
$configArr['parameters'] = 'c';
$filterpanel->attributes['constructor'] = $sfExtjs2Plugin->asMethod($configArr);
?]

<?php
  $moduleName = ucfirst(sfInflector::camelize($this->getModuleName()));
  $panelName = "List".$moduleName."TabPanel";
?>

[?php
// constructor
$configArr['source'] = "Ext.app.sx.<?php echo $panelName ?>.superclass.constructor.call(this, Ext.apply(".$sfExtjs2Plugin->asAnonymousClass($tabpanel->config_array).", c));";
$configArr['parameters'] = 'c';
$tabpanel->attributes['constructor'] = $sfExtjs2Plugin->asMethod($configArr);
?]

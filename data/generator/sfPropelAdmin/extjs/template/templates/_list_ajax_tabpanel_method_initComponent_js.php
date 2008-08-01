<?php
  $moduleName = ucfirst(sfInflector::camelize($this->getModuleName()));
  $panelName = "List".$moduleName."TabPanel";
?>

[?php
// constructor
$configArr['source'] = "Ext.app.sx.<?php echo $panelName ?>.superclass.initComponent.apply(this, arguments);";
$tabpanel->attributes['initComponent'] = $sfExtjs2Plugin->asMethod($configArr);
?]

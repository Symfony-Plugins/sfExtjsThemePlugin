<?php
  $moduleName = ucfirst(sfInflector::camelize($this->getModuleName()));
  $panelName = "List".$moduleName."TabPanel";
?>

[?php
// constructor
$configArr['source'] = "Ext.app.sx.<?php echo $panelName ?>.superclass.initEvents.apply(this);";
$tabpanel->attributes['initEvents'] = $sfExtjs2Plugin->asMethod($configArr);
?]

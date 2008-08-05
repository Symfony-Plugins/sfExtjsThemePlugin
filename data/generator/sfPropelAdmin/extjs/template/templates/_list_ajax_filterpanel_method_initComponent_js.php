<?php
  $moduleName = ucfirst(sfInflector::camelize($this->getModuleName()));
  $panelName = "List".$moduleName."FilterPanel";
?>

[?php
// constructor
$configArr['source'] = "Ext.app.sx.<?php echo $panelName ?>.superclass.initComponent.apply(this, arguments);";
$filterpanel->attributes['initComponent'] = $sfExtjs2Plugin->asMethod($configArr);
?]

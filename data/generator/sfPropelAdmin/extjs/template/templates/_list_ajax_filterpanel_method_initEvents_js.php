<?php
  $moduleName = ucfirst(sfInflector::camelize($this->getModuleName()));
  $panelName = "List".$moduleName."FilterPanel";
?>

[?php
// constructor
$configArr['source'] = "Ext.app.sx.<?php echo $panelName ?>.superclass.initEvents.apply(this);";
$filterpanel->attributes['initEvents'] = $sfExtjs2Plugin->asMethod($configArr);
?]

<?php
  $moduleName = ucfirst(sfInflector::camelize($this->getModuleName()));
  $rowactionsName = "List".$moduleName."RowActions";
?>

[?php
// initComponent
$configArr['source'] = "Ext.app.sx.<?php echo $rowactionsName ?>.superclass.initComponent.apply(this, arguments);";
$rowactions->attributes['initComponent'] = $sfExtjs2Plugin->asMethod($configArr);
?]

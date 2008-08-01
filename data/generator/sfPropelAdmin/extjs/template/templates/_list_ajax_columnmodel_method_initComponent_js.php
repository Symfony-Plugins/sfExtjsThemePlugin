<?php
  $moduleName = ucfirst(sfInflector::camelize($this->getModuleName()));
?>
[?php
// constructor
$configArr = array(
  'source' => "
    Ext.app.sx.<?php echo "List".$moduleName."ColumnModel" ?>.superclass.initComponent.apply(this, arguments);
  "
);

$columnmodel->attributes['initComponent'] = $sfExtjs2Plugin->asMethod($configArr);
?]

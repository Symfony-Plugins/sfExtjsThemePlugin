<?php
  $moduleName = ucfirst(sfInflector::camelize($this->getModuleName()));
  $cmName = "List".$moduleName."ColumnModel";

?>

[?php
$srcStr = "";

if($columnmodel->plugins)
{
  foreach($columnmodel->plugins as $key => $value)
  {
    $srcStr .= "\nthis.".$key." = Ext.ComponentMgr.create(".$sfExtjs2Plugin->asAnonymousClass($value).");";
  }
}
$srcStr .= "
    // columnmodel config
    this.cmConfig = [".substr($sfExtjs2Plugin->asAnonymousClass($columnmodel->config_array),1,-1)."];

    // combine <?php echo $cmName ?>Config with arguments
    Ext.app.sx.<?php echo $cmName ?>.superclass.constructor.call(this, Ext.apply(this.cmConfig, c));

    this.defaultSortable = <?php echo $this->getParameterValue('list.params.default_sortable', true) ? 'true': 'false' ?>;
";

// constructor
$configArr = Array(
  'parameters' => 'c',
  'source' => $srcStr
);

$columnmodel->attributes['constructor'] = $sfExtjs2Plugin->asMethod($configArr);
?]

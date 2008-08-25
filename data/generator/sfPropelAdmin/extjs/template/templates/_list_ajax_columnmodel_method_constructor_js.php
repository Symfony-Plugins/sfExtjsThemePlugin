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
  //I think this is desired as I don't see many instances where you would want the default renderer for a plugin
  if(isset($value['renderer']) && !strpos($value['renderer'], 'this')) unset($value['renderer']);
  $srcStr .= "\nthis.".$key." = Ext.ComponentMgr.create(".$sfExtjs2Plugin->asAnonymousClass($value).");";
}
}
$srcStr .= "
    // columnmodel config
    //TODO, this should be asAnonymousClass, when config_array really is an array, and not a javascript-object in a string
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

<?php
  $moduleName = ucfirst(sfInflector::camelize($this->getModuleName()));
  $cmName = "List".$moduleName."ColumnModel";
?>

[?php
// constructor
$configArr = Array(
  'parameters' => 'c',
  'source' => "
    // columnmodel config
    //TODO, this should be asAnonymousClass, when config_array really is an array, and not a javascript-object in a string
    this.cmConfig = [".substr($sfExtjs2Plugin->asAnonymousClass($columnmodel->config_array),1,-1)."];

    // combine <?php echo $cmName ?>Config with arguments
    Ext.app.sx.<?php echo $cmName ?>.superclass.constructor.call(this, Ext.apply(this.cmConfig, c));

    this.defaultSortable = <?php echo $this->getParameterValue('list.params.default_sortable', true) ? 'true': 'false' ?>;
  "
);

$columnmodel->attributes['constructor'] = $sfExtjs2Plugin->asMethod($configArr);
?]

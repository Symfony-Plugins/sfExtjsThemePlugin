<?php
  $moduleName = ucfirst(sfInflector::camelize($this->getModuleName()));
  $cmName = "List".$moduleName."ColumnModel";

?>

[?php
$srcStr = "";

// TODO: WARNING why only do credential checking when the columnmodel has credentials?!?!
if($columnmodel->plugins)
{
  foreach($columnmodel->plugins as $key => $value)
  {
    //I can't think of a better way to do this right now.
    //We have to determine available credentials during generation but can't compare them until page load
    //LvanderRee: I think it is OK like this.
    if(isset($value['editcreds']))
    {
      if(!$sf_user->hasCredential($value['editcreds'])) $value['editable']=false;
      unset($value['editcreds']);
    }
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

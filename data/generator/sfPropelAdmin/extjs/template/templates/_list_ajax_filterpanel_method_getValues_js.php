<?php
  $moduleName = ucfirst(sfInflector::camelize($this->getModuleName()));
  $panelName = "List".$moduleName."FilterPanel";
?>

[?php
$configArr = array(
  'parameters' => 'asString',
  'source' => "
  var values = [];
  this.form.items.each(function(field){
    if (field.name != undefined)
    {
      var value = (field.xtype=='checkbox')?((field.getValue())?1:''):field.getValue();
      values[field.name] = value;
    }
  });

  if(asString === true){
    return Ext.urlEncode(values);
  }
  return values;
"
);

$filterpanel->attributes['getValues'] = $sfExtjs2Plugin->asMethod($configArr);
?]

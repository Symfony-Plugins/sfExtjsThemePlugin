<?php
$moduleName = ucfirst(sfInflector::camelize($this->getModuleName()));
$cmName = "List".$moduleName.'ColumnModel';
$rendererName = "List".$moduleName.'Renderers';
$plugins = false;

// iterate through all (related) columns of all classes
$for = array('list.display');
// add grouping field, it not already in list.display
if ($this->hasGroupFieldNotInDisplay())
{
  $for[] = 'list.grouping.field';
}
$groupedColumns = $this->getColumnsGrouped($for, true);
$columns = $this->getListColumns($groupedColumns, false);
$columns = $this->sortColumns($columns);

$hs = $this->getParameterValue('list.hide', array());

$cmItems = array();
$credArr = array();
$i=0;
foreach ($columns as $column)
{
  $listcreds = $this->getParameterValue('list.fields.'.$column->key.'.credentials');
  if ($listcreds)
  {
    $listcreds = str_replace("\n", ' ', var_export($listcreds, true));
    $credArr[] = 'if(!$sf_user->hasCredential('.$listcreds.')) unset($columnmodel->config_array['.$i.']);';
  }

  if ($column->key == '*')
  {
    $cmItems[] = "{xtype: 'rowexpander'}";
    $i++;
    continue;
  }
  if (in_array($column->key, $hs) || ($column->isInvisible())) continue;

  if($this->getParameterValue('list.fields.'.$column->key.'.plugin'))
  {
    //setup the data for generating the new plugin instance
    $plugins[$column->key.'_'.$this->getParameterValue('list.fields.'.$column->key.'.plugin')] = $this->getColumnAjaxListDefinition($column, $groupedColumns);
    if ($editcreds = $this->getParameterValue('edit.fields.'.$column->key.'.credentials'))
    {
      $editcreds = str_replace("\n", ' ', var_export($editcreds, true));
      $plugins[$column->key.'_'.$this->getParameterValue('list.fields.'.$column->key.'.plugin')]['credstr'] = 'if(!$sf_user->hasCredential('.$editcreds.')) $value["editable"]=false;';
    }
    //set the column item to our generated plugin
    $cmItems[] = 'this.'.$column->key.'_'.$this->getParameterValue('list.fields.'.$column->key.'.plugin');
    $i++;
    continue;
  }

  if ($editcreds = $this->getParameterValue('edit.fields.'.$column->key.'.credentials'))
  {
    $editcreds = str_replace("\n", ' ', var_export($editcreds, true));
    $credArr[] = 'if(!$sf_user->hasCredential('.$editcreds.')&& is_array($columnmodel->config_array['.$i.'])&& isset($columnmodel->config_array['.$i.']["editor"])) unset($columnmodel->config_array['.$i.']["editor"]);';
  }

  if ($column->isPartial())
  {
    $cmItems[] = 'get_partial("gridcolumn_'.$column->getName().'")'; // TODO, maybe add $this->getSingularName() // TODO2: maybe maintain a second array of partials
  }
  else
  {
    $cmItems[] = $this->getColumnAjaxListDefinition($column, $groupedColumns);
  }
  $i++;
}
?>

[?php $columnmodel->config_array =
<?php echo preg_replace("/'(get_partial\([^\)]*\))',/", '\1,', var_export($cmItems, true)) ?>
; $columnmodel->plugins =
<?php var_export($plugins) ?>
; /* handle user credentials */
<?php echo implode("\n", $credArr) ?>
?]

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
  $credentials = $this->getParameterValue('list.fields.'.$column->key.'.credentials');
  if ($credentials){
    $credentials = str_replace("\n", ' ', var_export($credentials, true));
    $credArr[] = 'if(!$sf_user->hasCredential('.$credentials.')) unset($columnmodel->config_array['.$i.']);';
  }
  if ($column->key == '*')
  {
    $cmItems[] = "{xtype: 'rowexpander'}";
    continue;
  }
  if (in_array($column->key, $hs) || ($column->isInvisible())) continue;

  if($this->getParameterValue('list.fields.'.$column->key.'.plugin'))
  {
    $plugins[$column->key.'_'.$this->getParameterValue('list.fields.'.$column->key.'.plugin')] = $this->getColumnAjaxListDefinition($column, $groupedColumns);
    $cmItems[] = 'this.'.$column->key.'_'.$this->getParameterValue('list.fields.'.$column->key.'.plugin');
    continue;
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

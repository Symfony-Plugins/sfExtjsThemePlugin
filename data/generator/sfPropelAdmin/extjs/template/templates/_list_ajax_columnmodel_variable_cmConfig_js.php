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
  //handle credentials for displaying the column
  $listcreds = $this->getParameterValue('list.fields.'.$column->key.'.credentials');
  if ($listcreds)
  {
    $listcreds = str_replace("\n", ' ', var_export($listcreds, true));
    //if the user doesn't have the right permissions remove the columnconfig
    $credArr[] = 'if(!$sf_user->hasCredential('.$listcreds.')) unset($columnmodel->config_array['.$i.']);';
  }

  //captures the * in the list.display and sets the rowexpander at that position
  if ($column->key == '*')
  {
    $cmItems[] = "{xtype: 'rowexpander'}";
    $i++;
    continue;
  }

  //don't create column config for invisible columns
  if (($column->isInvisible()))
  {
    $i++;
    continue;
  }

  //TODO: figure out what we're actually supposed to do with list.hide columns, skipping for now
  if (in_array($column->key, $hs))
  {
    $i++;
    continue;
  }

  //handle edit credentials for plugin columns
  if($this->getParameterValue('list.fields.'.$column->key.'.plugin'))
  {
    $pluginArrName = $column->key.'_'.$this->getParameterValue('list.fields.'.$column->key.'.plugin');
    //setup the data for generating the new plugin instance
    $plugins[$pluginArrName] = $this->getColumnAjaxListDefinition($column, $groupedColumns);
    if ($editcreds = $this->getParameterValue('edit.fields.'.$column->key.'.credentials'))
    {
      $editcreds = str_replace("\n", ' ', var_export($editcreds, true));
      //pass our credentials down to the generated partial
      $credArr[] = "if(!\$sf_user->hasCredential($editcreds)&& is_array(\$columnmodel->plugins['$pluginArrName'])) \$columnmodel->plugins['$pluginArrName']['editable'] = false;";
    }
    //set the column item to our generated plugin
    $cmItems[] = 'this.'.$pluginArrName
    ;
    $i++;
    continue;
  }

  //handle edit credentials for non-plugin colmns
  if ($editcreds = $this->getParameterValue('edit.fields.'.$column->key.'.credentials'))
  {
    $editcreds = str_replace("\n", ' ', var_export($editcreds, true));
    //unset the editor if the user doesn't have the right credentials
    $credArr[] = "if(!\$sf_user->hasCredential($editcreds)&& is_array(\$columnmodel->config_array['$i'])&& isset(\$columnmodel->config_array['$i']['editor'])) unset(\$columnmodel->config_array['$i']['editor']);";
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

<?php
  $moduleName = ucfirst(sfInflector::camelize($this->getModuleName()));
  $cmName = "List".$moduleName.'ColumnModel';
  $rendererName = "List".$moduleName.'Renderers';

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
      $cmItems[] = 'Ext.app.sx.List'.$moduleName.'GridPanel.prototype.expander';
      continue;
    }
    if (in_array($column->key, $hs) || ($column->isInvisible())) continue;
    if ($column->isPartial())
    {
      $cmItems[] = get_partial('gridcolumn_<?php echo $column->getName() ?>'); // TODO, maybe add $this->getSingularName() ?]
    }
    else
    {
      $cmItems[] = $this->getColumnAjaxListDefinition($column, $groupedColumns);
    }
    $i++;
  }
?>

[?php $columnmodel->config_array = <?php var_export($cmItems) ?>;


/* handle user credentials */
<?php echo implode("\n", $credArr) ?>
?]

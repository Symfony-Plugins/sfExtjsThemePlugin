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

//  $first = true;
?>
  [?php $cmItems = array(); ?]
  <?php foreach ($columns as $column): ?>
    <?php if ($column->key == '*'): ?>
      [?php $cmItems[] = "Ext.app.sx.List<?php echo $moduleName?>GridPanel.prototype.expander" ?]
      <?php continue ?>
    <?php endif;

    if (in_array($column->key, $hs) || ($column->isInvisible())) continue;

    $credentials = $this->getParameterValue('list.fields.'.$column->key.'.credentials');
?>
<?php if ($credentials): $credentials = str_replace("\n", ' ', var_export($credentials, true)) ?>
[?php if ($sf_user->hasCredential(<?php echo $credentials ?>)): ?]
<?php endif; ?>
<?php if ($column->isPartial()): ?>
  [?php $cmItems[] = get_partial('gridcolumn_<?php echo $column->getName() ?>') // TODO, maybe add $this->getSingularName() ?]
<?php else: ?>
  [?php $cmItems[] = <?php var_export($this->getColumnAjaxListDefinition($column, $groupedColumns)) ?> ?]
<?php endif; ?>
<?php if ($credentials): ?>
[?php endif; ?]
<?php endif; ?>
  <?php endforeach ?>

[?php $columnmodel->config_array = $cmItems ?]
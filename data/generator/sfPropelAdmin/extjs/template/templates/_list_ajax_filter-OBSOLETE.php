<?php
  $list_ns = ucfirst(sfInflector::camelize($this->getModuleName()))."List";

  $limit = $this->getParameterValue('list.max_per_page', sfConfig::get('app_sf_extjs_theme_plugin_list_max_per_page', 20));

  //TODO: take a look at: http://www.sk-typo3.de/index.php?id=345

  // iterate through all (related) columns of all classes
  $for = 'list.filters';
  $groupedColumns = $this->getColumnsGrouped($for);
  $columns = $this->getListColumns($groupedColumns);
  $tableName = $this->getTableName();

  // "sort" output on index, since index should be unique, this is easy
  // first create a new array
  $temp = $formFields = array();
  foreach ($columns as $column)
  {
    $temp[$column->index] = $column;
  }
  // do real sortining
  ksort($temp);
  // put sorted array back
  $columns  = $temp;

  foreach ($columns as $column):
    $type = $column->getCreoleType();
    $columnName = $column->key;
?>
<?php $credentials = $this->getParameterValue('list.fields.'.$columnName.'.credentials') ?>
<?php if ($credentials): $credentials = str_replace("\n", ' ', var_export($credentials, true)) ?>
    [?php if ($sf_user->hasCredential(<?php echo $credentials ?>)): ?]
<?php endif; ?>
<?php
  //TODO, change this so drop-down columnboxes and checkboxes appear...
  $formFields[] = array('fieldLabel' => str_replace("'", "\\'", $this->getParameterValue('list.fields.'.$columnName.'.name')), 'name' => 'filters['.str_replace('/', $this->tableDelimiter, $columnName).']');
?>
<?php if ($credentials): ?>
    [?php endif; ?]
<?php endif; ?>
<?php endforeach; ?>
<?php
$buttons = array
(
  $this->sfExtjs2Plugin->Button(array
  (
    'text'    => '[?php echo __(\'Filter\') ?]',
    'handler' => $this->sfExtjs2Plugin->asMethod($list_ns.'.getDataStore().baseParams={filter:1}; var params=formPanel.form.getValues(); params.start=0; params.limit='.$limit.'; '.$list_ns.'.getDataStore().load({params:params});')
  )),
  $this->sfExtjs2Plugin->Button(array
  (
    'text'    => '[?php echo __(\'Reset\') ?]',
    'handler' => $this->sfExtjs2Plugin->asMethod($list_ns.'.getDataStore().baseParams=""; formPanel.form.reset(); '.$list_ns.'.getDataStore().load({params:{start:0,limit:'.$limit.'}});')
  ))
);

echo $this->sfExtjs2Plugin->FormPanel(array
(
  'name'       => 'formPanel',
  'attributes' => array
  (
    'title'      => '[?php echo __(\'Filters\') ?]',
    'autoScroll' => true,
    'bodyStyle'  => 'padding: 5px 0px 0px 10px; position: relative;',
    'labelAlign' => 'top',
    'defaults'   => array('xtype' => 'textfield', 'anchor' => '85%'),
    'items'      => $formFields,
    'buttons'    => $buttons
  )
)); ?>


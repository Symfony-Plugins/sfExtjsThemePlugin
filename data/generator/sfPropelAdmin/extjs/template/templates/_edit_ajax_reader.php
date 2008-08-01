<?php
  sfLoader::loadHelpers('Extjs');

  $moduleName = sfInflector::camelize($this->getModuleName());

  $edit_key = 'edit';
  $for = $edit_key.'.display';

  // iterate through all (related) columns of all classes
  $groupedColumns = $this->getColumnsGrouped($for);
  $columns = $this->getListUniqueColumns($groupedColumns, true);
  $pkn = $groupedColumns['pk']->getName();
  $tableName = $groupedColumns['pk']->getTableName();



  $options = array(
    'id' => $pkn,
    'root' => 'data',
    'totalProperty' => 'totalCount'
  );

  $display = array();
  foreach ($columns as $columnName => $column)
  {
    $fieldName = str_replace('/', $this->tableDelimiter, $column->key);

    $display[] = array(
      'name' => $tableName.'['.$fieldName.']',
      'mapping' => $fieldName,
      'type' => $this->getFieldTypeForReader($column)
    );
  }

  echo extjs_data_reader($options, $display, 'json');

?>

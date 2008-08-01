[?php /* * Created on 20-nov-2007 * * by Leon van der Ree */ ?]
<?php
  $moduleName = ucfirst(sfInflector::camelize($this->getModuleName()));

  // TODO: parts of this should be moved to the actions.class one day
  // iterate through all (related) columns of all classes
  $for = 'list.display';
  $groupedColumns = $this->getColumnsGrouped($for);
  $columns = $this->getListUniqueColumns($groupedColumns, true);

  $hs = $this->getParameterValue('list.hide', array());

  // set group field
  $group_field = $this->getParameterValue('list.grouping.field', null);
  if ($group_field)
  {
    $group_field = str_replace('/', $this->tableDelimiter, $group_field);
  }

  $store = 'Store'; if ($group_field) $store = 'Grouping'.$store;
  $storeName = "List".$moduleName.$store;

  $listDisplay = array();

  foreach ($columns as $column)
  {
    if ($column->isPartial()) continue; //partials will not end up in json-data

    $columnName = $column->key;
    $fieldName = str_replace('/', $this->tableDelimiter, $columnName);

    $listDisplay[] = $this->sfExtjs2Plugin->asAnonymousClass(array
                     (
                       'name' => $fieldName,
                       //mapping is not required if it is the same as the name
                       //'mapping' => $fieldName,
                       'type' => $this->getFieldTypeForReader($column)
                     ));
  }

  $jsReader = $this->sfExtjs2Plugin->JsonReader(array
  (
    'id'            => $groupedColumns['pk']->getName(),
    'root'          => 'data',
    'totalProperty' => 'totalCount',
    'fields'        => $listDisplay
  ));

  $jsProxy = $this->sfExtjs2Plugin->HttpProxy(array
  (
    'url' => $this->controller->genUrl($this->getModuleName().'/jsonList'),
    'method' => 'POST'
  ));

  $options = array
  (
    'proxy' => $jsProxy,
    'reader' => $jsReader
  );

  if ($group_field)
  {
    $options['groupField'] = $group_field;
    $options['remoteGroup'] = 'true';
    $options['sortInfo'] = array
    (
      'field' => $group_field,
      'direction' => 'asc'
    );
  }
  $options['remoteSort'] = 'true';

  $config = $this->sfExtjs2Plugin->asAnonymousClass($options);
?>

[?php
//dsConfig
$sfExtjs2_dsConfig = "<?php echo $config ?>";

// constructor
$sfExtjs2_<?php echo $storeName ?>_constructor = "
  // combine <?php echo $storeName ?>Config with arguments
  Ext.app.sx.<?php echo $storeName ?>.superclass.constructor.call(this, Ext.apply(this.storeConfig, c));
";

// initComponent
$sfExtjs2_<?php echo $storeName ?>_initComponent = "
  //call parent
  Ext.app.sx.<?php echo $storeName ?>.superclass.initComponent.apply(this, arguments);
";

// app.sx from Symfony eXtended (instead of ux: user eXtention)
$sfExtjs2Plugin->beginClass(
  'Ext.app.sx',
  '<?php echo $storeName ?>',
  'Ext.data.<?php echo $store ?>',
  array (
    'storeConfig' => $sfExtjs2Plugin->asVar($sfExtjs2_dsConfig),
    'constructor'   => $sfExtjs2Plugin->asMethod(array(
      'parameters' => 'c',
      'source'     => $sfExtjs2_<?php echo $storeName ?>_constructor
    )),
    'initComponent' => $sfExtjs2Plugin->asMethod($sfExtjs2_<?php echo $storeName ?>_initComponent),
  )
);
$sfExtjs2Plugin->endClass();
?]
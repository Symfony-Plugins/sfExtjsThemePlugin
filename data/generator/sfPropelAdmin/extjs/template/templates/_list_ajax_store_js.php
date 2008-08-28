<?php
  $moduleName = ucfirst(sfInflector::camelize($this->getModuleName()));

  // TODO: parts of this should be moved to the actions.class one day
  // iterate through all (related) columns of all classes
  if($this->getParameterValue('list.expand_column'))
  {
    //$list = $this->getParameterValue('list.display');
    //$this->setParameterValue('list.display') = array_push($list,'+'.$this->getParameterValue('list.expand_column'));
  }
  $for = 'list.display';
  $groupedColumns = $this->getColumnsGrouped($for);
  $columns = $this->getListUniqueColumns($groupedColumns, true);

  $hs = $this->getParameterValue('list.hide', array());

  $sort_field = $this->getParameterValue('list.sort', null);
  $sort_dir = 'asc';
  if (is_array($sort_field))
  {
    $sort_dir   = $sort_field[1];
    $sort_field = $sort_field[0];
  }
  // set group field
  $group_field = $this->getParameterValue('list.grouping.field', null);
  if ($group_field)
  {
    $group_field = str_replace('/', $this->tableDelimiter, $group_field);
  }

  $store = 'Store'; if ($group_field) $store = 'Grouping'.$store;
  $panelName = "List".$moduleName.$store;
  $panelName_xtype = "list".$this->getModuleName().$store;

  $listDisplay = array();
  $credArr = array();

  $i=0;
  foreach ($columns as $column)
  {
    if ($column->key == '*') continue;  //expander column not in json-data
    if ($column->isPartial()) continue; //partials will not end up in json-data

    $columnName = $column->key;
    $fieldName = str_replace('/', $this->tableDelimiter, $columnName);

    $credentials = $this->getParameterValue('list.fields.'.$columnName.'.credentials');
    if ($credentials){
      $credentials = str_replace("\n", ' ', var_export($credentials, true));
      $credArr[] = 'if(!$sf_user->hasCredential('.$credentials.')) unset($reader["fields"]['.$i.']);';
    }

    $listDisplay[] = array(
     'name' => $fieldName,
     'type' => $this->getFieldTypeForReader($column)
    );
    $i++;
  }

  $jsonReader = array(
    'id'            => $groupedColumns['pk']->getName(),
    'root'          => 'data',
    'totalProperty' => 'totalCount',
    'fields'        => $listDisplay
  );

  $httpProxy = array(
    'url' => $this->controller->genUrl($this->getModuleName().'/jsonList'),
    'method' => 'POST'
  );

  $options = array(
    //'proxy' => $httpProxy,
    //'reader' => $jsonReader
  );

  //set default sort, can be overruled by groupfield
  if ($sort_field)
  {
    $options['sortInfo'] = array(
      'field'     => str_replace('/', $this->tableDelimiter, $sort_field),
      'direction' => $sort_dir
    );
  }
  if ($group_field)
  {
    $options['groupField'] = $group_field;
    $options['remoteGroup'] = 'true';
    $options['sortInfo'] = array(
      'field' => $group_field,
      'direction' => 'asc'
    );
  }
  $options['remoteSort'] = 'true';

?>

[?php
$store = new stdClass();
$store->attributes = array();

/* Store Configuration */

// default config
$store->config_array = <?php var_export($options) ?>;
$store->config_array['proxy'] = $sfExtjs2Plugin->HttpProxy(<?php var_export($httpProxy) ?>);

$reader = <?php var_export($jsonReader) ?>;

/* handle user credentials */
<?php echo implode("\n", $credArr) ?>


$store->config_array['reader'] = $sfExtjs2Plugin->JsonReader($reader);
$panelName = '<?php echo $panelName ?>';

/* Datastore methods and variables */

// constructor
include_partial('list_ajax_store_method_constructor_js', array('sfExtjs2Plugin' => $sfExtjs2Plugin, 'store' => $store, 'panelName' => $panelName));

// initComponent
include_partial('list_ajax_store_method_initComponent_js', array('sfExtjs2Plugin' => $sfExtjs2Plugin, 'store' => $store, 'panelName' => $panelName));

// initEvents
include_partial('list_ajax_store_method_initEvents_js', array('sfExtjs2Plugin' => $sfExtjs2Plugin, 'store' => $store, 'panelName' => $panelName));

<?php
$methods =  $this->getParameterValue('datastore.method');
if(isset($methods['partials'])):
if (!is_array($methods['partials']))
{
  $methods['partials'] = array($methods['partials']);
}
?>
// generator method partials
<?php
  foreach($methods['partials'] as $method):
?>
include_partial('<?php echo substr($method,1) ?>', array('sfExtjs2Plugin' => $sfExtjs2Plugin, 'store' => $store));
<?php
    $this->createPartialFile($method,'<?php // @object $sfExtjs2Plugin and @object $store provided ?>');
  endforeach;
endif;

$variables =  $this->getParameterValue('datastore.variable');
if (isset($variables['partials'])):
if (!is_array($variables['partials']))
{
  $variables['partials'] = array($variables['partials']);
}
?>
// generator variable partials
<?php
  foreach($variables['partials'] as $variable):
?>
include_partial('<?php echo substr($variable,1) ?>', array('sfExtjs2Plugin' => $sfExtjs2Plugin, 'store' => $store));
<?php
    $this->createPartialFile($variable,'<?php // @object $sfExtjs2Plugin and @object $store provided ?>');
  endforeach;
endif;
?>

// create the Ext.app.sx.<?php echo $panelName ?> class
$sfExtjs2Plugin->beginClass(
  'Ext.app.sx',
  '<?php echo $panelName ?>',
  'Ext.data.<?php echo $store ?>',
  $store->attributes
);

$sfExtjs2Plugin->endClass();
?]
// register xtype
Ext.reg('<?php echo $panelName_xtype ?>', Ext.app.sx.<?php echo $panelName ?>);

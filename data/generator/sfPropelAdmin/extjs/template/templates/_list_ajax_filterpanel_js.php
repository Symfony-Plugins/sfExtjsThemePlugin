<?php
  $moduleName = ucfirst(sfInflector::camelize($this->getModuleName()));
  $panelName = "List".$moduleName."FilterPanel";
  $panelName_xtype = "list".$this->getModuleName()."filterpanel";
  $limit = $this->getParameterValue('list.max_per_page', sfConfig::get('app_sf_extjs_theme_plugin_list_max_per_page', 20));

  // iterate through all (related) columns of all classes
  $for = 'list.filters';
  $groupedColumns = $this->getColumnsGrouped($for);
  $columns = $this->getListColumns($groupedColumns);
  $tableName = $this->getTableName();

  $temp = $formFields = array();
  $credArr = array();
  foreach ($columns as $column)
  {
    $temp[$column->index] = $column;
  }
  // do real sorting
  ksort($temp);
  // put sorted array back
  $columns  = $temp;

  $i = 0;
  foreach ($columns as $column){
    $type = $column->getCreoleType();
    $columnName = $column->key;
    $credentials = $this->getParameterValue('list.fields.'.$columnName.'.credentials');
    if ($credentials){
      $credentials = str_replace("\n", ' ', var_export($credentials, true));
      $credArr[] = 'if(!$sf_user->hasCredential('.$credentials.')) unset($filterpanel->config_array["items"]['.$i.']);';
    }

    $fieldArr = $this->getColumnAjaxFilterDefinition($column, $groupedColumns);

    if(isset($fieldArr['renderer'])) unset($fieldArr['renderer']);
    $formFields[] = $fieldArr;

    //TODO, change this so drop-down columnboxes and checkboxes appear...
    //$formFields[] = array('fieldLabel' => str_replace("'", "\\'", $this->getParameterValue('list.fields.'.$columnName.'.name')), 'name' => 'filters['.str_replace('/', $this->tableDelimiter, $columnName).']');
    $i++;
  }
?>
[?php
$filterpanel = new stdClass();
$filterpanel->attributes = array();

/* FilterPanel Configuration */

// default config
$filterpanel->config_array = array(
  'deferredRender'      => true,
  'title'      => 'Filters',
  'autoScroll' => true,
  'bodyStyle'  => 'padding: 5px 0px 0px 10px; position: relative;',
  'labelAlign' => 'top',
  'defaults'   => array('xtype' => 'textfield', 'anchor' => '85%'),
  'items'      => <?php var_export($formFields) ?>,
  'buttons'    => array(
    $sfExtjs2Plugin->Button(array
    (
      'text'    => 'Filter',
      //TODO:  Handler needs work
      'handler' => $sfExtjs2Plugin->asMethod("
        var formpanel = (typeof this.scope != 'undefined')? this.scope : this;
        var params=formpanel.form.getValues();
        params.start=0;
        params.limit=<?php echo $limit ?>;
        formpanel.fireEvent('filter_set', params, this);
      "),
      'scope' => 'this'
    )),
    $sfExtjs2Plugin->Button(array
    (
      'text'    => 'Reset',
      //TODO:  Handler needs work
      'handler' => $sfExtjs2Plugin->asMethod("
        var formpanel = (typeof this.scope != 'undefined')? this.scope : this;
        formpanel.form.reset();
        formpanel.fireEvent('filter_reset', this);
      "),
      'scope' => 'this'
    ))
  )
);

/* handle user credentials */
<?php echo implode("\n", $credArr) ?>


/* FilterPanel methods and variables */

// constructor
include_partial('list_ajax_filterpanel_method_constructor_js', array('sfExtjs2Plugin' => $sfExtjs2Plugin, 'filterpanel' => $filterpanel));

// initComponent
include_partial('list_ajax_filterpanel_method_initComponent_js', array('sfExtjs2Plugin' => $sfExtjs2Plugin, 'filterpanel' => $filterpanel));

// initEvents
include_partial('list_ajax_filterpanel_method_initEvents_js', array('sfExtjs2Plugin' => $sfExtjs2Plugin, 'filterpanel' => $filterpanel));


<?php
$methods =  $this->getParameterValue('filterpanel.method');
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
include_partial('<?php echo substr($method,1) ?>', array('sfExtjs2Plugin' => $sfExtjs2Plugin, 'filterpanel' => $filterpanel));
<?php
    $this->createPartialFile($method,'<?php // @object $sfExtjs2Plugin and @object $filterpanel provided ?>');
  endforeach;
endif;

$variables =  $this->getParameterValue('filterpanel.variable');
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
include_partial('<?php echo substr($variable,1) ?>', array('sfExtjs2Plugin' => $sfExtjs2Plugin, 'filterpanel' => $filterpanel));
<?php
    $this->createPartialFile($variable,'<?php // @object $sfExtjs2Plugin and @object $filterpanel provided ?>');
  endforeach;
endif;
?>

// create the Ext.app.sx.<?php echo $panelName ?> class
$sfExtjs2Plugin->beginClass(
  'Ext.app.sx',
  '<?php echo $panelName ?>',
  'Ext.FormPanel',
  $filterpanel->attributes
);

$sfExtjs2Plugin->endClass();
?]
// register xtype
Ext.reg('<?php echo $panelName_xtype ?>', Ext.app.sx.<?php echo $panelName ?>);
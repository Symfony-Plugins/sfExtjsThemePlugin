<?php
  $moduleName = ucfirst(sfInflector::camelize($this->getModuleName()));
  $panelName = "List".$moduleName."GridPanel";
  $panelName_xtype = strtolower("List".$this->getModuleName()."GridPanel");

  $group_field = $this->getParameterValue('list.grouping.field', null);
  $grid_view_extras = $this->getParameterValue('list.grid_view_extras', '');

  $limit = $this->getParameterValue('list.max_per_page', sfConfig::get('app_sf_extjs_theme_plugin_list_max_per_page', 20));
  $pluginArr = false;

  if($this->getParameterValue('list.plugins'))
  {
    $pluginArr = (!is_array($this->getParameterValue('list.plugins'))) ? array($this->getParameterValue('list.plugins')) : $this->getParameterValue('list.plugins');

  }

  foreach($this->getParameterValue('list.display') as $col)
  {
    if($this->getParameterValue('list.fields.'.$col.'.plugin')) $pluginArr[] = 'this.cm.'.$this->getParameterValue('list.fields.'.$col.'.plugin');
  }
?>
[?php
$gridpanel = new stdClass();
$gridpanel->attributes = array();

/* gridPanel Configuration */

<?php $objectName = $this->getParameterValue('object_name', $this->getModuleName()) ?>

$sfExtjs2_gridpanel_view = 'new Ext.grid.GridView({forceFit: true, autoFill: true <?php echo $grid_view_extras ?>})';
$gridpanel->column_model = 'new Ext.app.sx.<?php echo 'List'.$moduleName.'ColumnModel' ?>()';

// default config
$gridpanel->config_array = array(
  'title'               => <?php echo $this->getI18NString('list.title', $objectName.' overview', false) ?>,
  'ds'                  => $sfExtjs2Plugin->asVar('new Ext.app.sx.<?php echo 'List'.$moduleName.(($group_field)?'Grouping':'Store') ?>()'),
  'cm'                  => 'this.cm',
  'view'                => $sfExtjs2Plugin->asVar($sfExtjs2_gridpanel_view),
  'autoScroll'          => true,  //needed to set a height on the toolbar so the scroll doesnt mess up when adding buttons to an empty bar
  'autoLoadStore'       => true,
  'selModel'            => $sfExtjs2Plugin->RowSelectionModel(array(
                            'singleSelect' => <?php echo $this->getParameterValue('list.single_select', true) ? 'true' : 'false' ?> // this should probably also be defined application wide
                           )),
  'clicksToEdit'        => <?php var_export(sfConfig::get('app_sf_extjs_theme_plugin_list_clicksToEdit', 1)) ?>,
  'trackMouseOver'      => <?php var_export(sfConfig::get('app_sf_extjs_theme_plugin_list_trackMouseOver', false)) ?>, //this will cause the firefox permission denied errors if true
  'loadMask'            => <?php var_export(sfConfig::get('app_sf_extjs_theme_plugin_list_loadMask', false)) ?>,
);

<?php if (sfConfig::get('app_sf_extjs_theme_plugin_list_tabbed')): ?>
$gridpanel->config_array['header'] = false;
<?php endif; ?>

// get plugins from generator
<?php if(is_array($pluginArr)): ?>
$gridpanel->config_array['plugins'] = <?php echo var_export($pluginArr); ?>;
<?php endif; ?>

// get autoExpandColumn from generator
<?php if($this->getParameterValue('list.auto_expand_column')): ?>
$gridpanel->config_array['autoExpandColumn'] = '<?php echo $this->getParameterValue('list.auto_expand_column') ?>';
<?php endif; ?>
<?php
  $user_params = $this->getParameterValue('list.params', array());

  if (isset($user_params['bbar'])) unset($user_params['bbar']);
  if (isset($user_params['tbar'])) unset($user_params['tbar']);

  if (is_array($user_params)):
?>
$gridpanel->config_array = array_merge($gridpanel->config_array, <?php var_export($user_params) ?>);
<?php endif; ?>

/* gridPanel methods and variables */

// constructor
include_partial('list_ajax_gridpanel_method_constructor_js', array('sfExtjs2Plugin' => $sfExtjs2Plugin, 'gridpanel' => $gridpanel));

// initComponent
include_partial('list_ajax_gridpanel_method_initComponent_js', array('sfExtjs2Plugin' => $sfExtjs2Plugin, 'gridpanel' => $gridpanel));

// initEvents
include_partial('list_ajax_gridpanel_method_initEvents_js', array('sfExtjs2Plugin' => $sfExtjs2Plugin, 'gridpanel' => $gridpanel));

// onRender
include_partial('list_ajax_gridpanel_method_onRender_js', array('sfExtjs2Plugin' => $sfExtjs2Plugin, 'gridpanel' => $gridpanel));

// onLinkClick
include_partial('list_ajax_gridpanel_method_onLinkClick_js', array('sfExtjs2Plugin' => $sfExtjs2Plugin, 'gridpanel' => $gridpanel));

// updateDB
include_partial('list_ajax_gridpanel_method_updateDB_js', array('sfExtjs2Plugin' => $sfExtjs2Plugin, 'gridpanel' => $gridpanel));

// setFilter
$gridpanel->attributes['setFilter'] = $sfExtjs2Plugin->asMethod(array(
  'parameters' => 'params',
  'source' => "
    this.store.baseParams={filter:1};
    this.store.load({params:params});
"));

// resetFilter
$gridpanel->attributes['resetFilter'] = $sfExtjs2Plugin->asMethod("
  this.store.baseParams='';
  this.store.load({params:{start:0,limit:<?php echo $limit ?>}});
");

// create the getters
// have to do this as lcfirst is still in php cvs
//TODO: move this to a library!
function lcfirst( $str ) {
  $str[0] = strtolower($str[0]);
  return (string)$str;
}

$getterArr = array('getModulename','getPanelType');
foreach($getterArr as $getter)
{
  $configArr = array();
  $configArr['source'] = "return this.".lcfirst(substr($getter,3));
  $gridpanel->attributes[$getter] = $sfExtjs2Plugin->asMethod($configArr);
}

<?php
$methods =  $this->getParameterValue('gridpanel.method');
if (isset($methods['partials'])):
if (!is_array($methods['partials']))
{
  $methods['partials'] = array($methods['partials']);
}
?>
// generator method partials
<?php
  foreach($methods['partials'] as $method):
?>
include_partial('<?php echo substr($method,1) ?>', array('sfExtjs2Plugin' => $sfExtjs2Plugin, 'gridpanel' => $gridpanel));
<?php
    $this->createPartialFile($method,'<?php // @object $sfExtjs2Plugin and @object $gridpanel provided ?>');
  endforeach;
endif;

$variables =  $this->getParameterValue('gridpanel.variable');
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
include_partial('<?php echo substr($variable,1) ?>', array('sfExtjs2Plugin' => $sfExtjs2Plugin, 'gridpanel' => $gridpanel));
<?php
  $this->createPartialFile($variable,'<?php // @object $sfExtjs2Plugin and @object $gridpanel provided ?>');
  endforeach;
endif;
?>

// create the Ext.app.sx.<?php echo $panelName ?> class
$sfExtjs2Plugin->beginClass(
  'Ext.app.sx',
  '<?php echo $panelName ?>',
  'Ext.grid.EditorGridPanel',
  $gridpanel->attributes
);
$sfExtjs2Plugin->endClass();
?]
// register xtype
Ext.reg('<?php echo $panelName_xtype ?>', Ext.app.sx.<?php echo $panelName ?>);
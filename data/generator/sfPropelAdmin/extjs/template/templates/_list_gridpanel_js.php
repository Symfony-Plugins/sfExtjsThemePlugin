<?php
  $moduleName = ucfirst(sfInflector::camelize($this->getModuleName()));
  $className = "List".$moduleName."GridPanel";
  $xtype = "list".$this->getModuleName()."gridpanel";
  $panelName_xtype = strtolower($className);
  $group_field = $this->getParameterValue('list.grouping.field', false);
  $objectName = $this->getParameterValue('object_name', $this->getModuleName());
  $limit = $this->getParameterValue('list.max_per_page', sfConfig::get('sf_extjs_theme_plugin_list_max_per_page', 20));
  $gridConfig = $this->getGridPanelConfig();
?>
[?php
$className = '<?php echo $className ?>';
$gridpanel = new stdClass();
$gridpanel->attributes = array();

/* gridPanel Configuration */

$sfExtjs2_gridpanel_view = 'new Ext.grid.<?php echo (($group_field)?'Grouping':'Grid') ?>View()';

<?php if (!empty($gridConfig['expander_partial'])): ?>
// initialise the row expander plugin
$gridpanel->rowExpander = 'this.getRowExpander();';

<?php endif; ?>
$gridpanel->column_model = 'new Ext.app.sx.<?php echo 'List'.$moduleName.'ColumnModel' ?>()';

// default config
$gridpanel->config_array = array(
  'title'               => <?php echo $this->getI18NString('list.title', $objectName.' overview', false) ?>,
  'ds'                  => $sfExtjs2Plugin->asVar('new Ext.app.sx.<?php echo 'List'.$moduleName.(($group_field)?'GroupingStore':'Store') ?>()'),
  'cm'                  => 'this.cm',
  'view'                => $sfExtjs2Plugin->asVar($sfExtjs2_gridpanel_view),
  'autoScroll'          => true,  //needed to set a height on the toolbar so the scroll doesnt mess up when adding buttons to an empty bar
  'autoLoadStore'       => true,
  'clicksToEdit'        => 1,
  'trackMouseOver'      => false, //this will cause the firefox permission denied errors if true
  'loadMask'            => false,
  'stripeRows'          => true,
  'viewConfig'          => $sfExtjs2Plugin->asAnonymousClass(array('forceFit'=>true)),
);

<?php if (sfConfig::get('sf_extjs_theme_plugin_list_tabbed')): ?>
$gridpanel->config_array['header'] = false;

<?php endif; ?>
// get plugins from generator
<?php if(is_array($gridConfig['plugin_arr'])): ?>
$gridpanel->config_array['plugins'] = <?php echo var_export($gridConfig['plugin_arr']); ?>;

<?php endif; ?>
<?php if (is_array($gridConfig['user_params'])):?>

$gridpanel->config_array = array_merge($gridpanel->config_array, <?php var_export($gridConfig['user_params']) ?>);
<?php endif; ?>
/* gridPanel methods and variables */

// constructor
include_partial('list_gridpanel_method_constructor_js', array('sfExtjs2Plugin' => $sfExtjs2Plugin, 'gridpanel' => $gridpanel, 'className' => $className));

// initComponent
include_partial('list_gridpanel_method_initComponent_js', array('sfExtjs2Plugin' => $sfExtjs2Plugin, 'gridpanel' => $gridpanel, 'className' => $className));

// initEvents
include_partial('list_gridpanel_method_initEvents_js', array('sfExtjs2Plugin' => $sfExtjs2Plugin, 'gridpanel' => $gridpanel, 'className' => $className));

// onRender
include_partial('list_gridpanel_method_onRender_js', array('sfExtjs2Plugin' => $sfExtjs2Plugin, 'gridpanel' => $gridpanel, 'className' => $className));

// onLinkClick
include_partial('list_gridpanel_method_onLinkClick_js', array('sfExtjs2Plugin' => $sfExtjs2Plugin, 'gridpanel' => $gridpanel, 'className' => $className));

// updateDB
include_partial('list_gridpanel_method_updateDB_js', array('sfExtjs2Plugin' => $sfExtjs2Plugin, 'gridpanel' => $gridpanel, 'className' => $className));
<?php echo $this->getCustomPartials('gridpanel','method'); ?>
<?php echo $this->getCustomPartials('gridpanel','variable'); ?>
<?php echo $gridConfig['expander_partial'] ?>

// setFilter
$gridpanel->attributes['setFilter'] = $sfExtjs2Plugin->asMethod(array(
  'parameters' => 'params',
  'source' => "
    this.store.baseParams.filter = 'query';
    this.store.load({params:params});
"));

// resetFilter
$gridpanel->attributes['resetFilter'] = $sfExtjs2Plugin->asMethod("
  this.store.baseParams.filter = null;
  this.store.load({params:{start:0,limit:<?php echo $limit ?>}});
");

<?php echo $this->getClassGetters('gridpanel',array('modulename','panelType')); ?>

// create the Ext.app.sx.<?php echo $className ?> class
$sfExtjs2Plugin->beginClass(
  'Ext.app.sx',
  '<?php echo $className ?>',
  'Ext.grid.EditorGridPanel',
  $gridpanel->attributes
);
$sfExtjs2Plugin->endClass();
?]
// register xtype
Ext.reg('<?php echo $xtype ?>', Ext.app.sx.<?php echo $className ?>);
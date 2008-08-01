<?php
  $moduleName = ucfirst(sfInflector::camelize($this->getModuleName()));
  $panelName = "List".$moduleName."TabPanel";
?>
[?php
$tabpanel = new stdClass();
$tabpanel->attributes = array();

/* tabPanel Configuration */

// default config
$tabpanel->config_array = array(
  'deferredRender'      => true,
  'enableTabScroll'     => true,
  'tabWidth'            => 200
);

// check generator for plugins, always use Ext.ux.TabCloseMenu
$tabpanel->config_array['plugins'] = array('new Ext.ux.TabCloseMenu()'<?php if($this->getParameterValue('tabpanel.plugins')) echo "','".$this->getParameterValue('tabpanel.plugins')."'" ?>);

// check if activeTab is defined in the generator or use 0 as default
$tabpanel->config_array['activeTab'] = '<?php echo $this->getParameterValue('tabpanel.activeTab', 0) ?>';

/* tabPanel methods and variables */

// constructor
include_partial('list_ajax_tabpanel_method_constructor_js', array('sfExtjs2Plugin' => $sfExtjs2Plugin, 'tabpanel' => $tabpanel));

// initComponent
include_partial('list_ajax_tabpanel_method_initComponent_js', array('sfExtjs2Plugin' => $sfExtjs2Plugin, 'tabpanel' => $tabpanel));

// initEvents
include_partial('list_ajax_tabpanel_method_initEvents_js', array('sfExtjs2Plugin' => $sfExtjs2Plugin, 'tabpanel' => $tabpanel));

<?php
$methods =  $this->getParameterValue('tabpanel.method');
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
include_partial('<?php echo substr($method,1) ?>', array('sfExtjs2Plugin' => $sfExtjs2Plugin, 'tabpanel' => $tabpanel));
<?php
    $this->createPartialFile($method,'<?php // @object $sfExtjs2Plugin and @object $tabpanel provided ?>');
  endforeach;
endif;

$variables =  $this->getParameterValue('tabpanel.variable');
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
include_partial('<?php echo substr($variable,1) ?>', array('sfExtjs2Plugin' => $sfExtjs2Plugin, 'tabpanel' => $tabpanel));
<?php
    $this->createPartialFile($variable,'<?php // @object $sfExtjs2Plugin and @object $tabpanel provided ?>');
  endforeach;
endif;
?>

// create the Ext.app.sx.<?php echo $panelName ?> class
$sfExtjs2Plugin->beginClass(
  'Ext.app.sx',
  '<?php echo $panelName ?>',
  'Ext.TabPanel',
  $tabpanel->attributes
);

$sfExtjs2Plugin->endClass();
?]
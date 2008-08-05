<?php
  $moduleName = ucfirst(sfInflector::camelize($this->getModuleName()));
  $panelName = "List".$moduleName."FilterPanel";
?>
[?php
$filterpanel = new stdClass();
$filterpanel->attributes = array();

/* FilterPanel Configuration */

// default config
$filterpanel->config_array = array(
  'deferredRender'      => true
);

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
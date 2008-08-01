<?php
  $moduleName = ucfirst(sfInflector::camelize($this->getModuleName()));
  $cmName = "List".$moduleName.'ColumnModel';
  $rendererName = "List".$moduleName.'Renderers';
?>
[?php

$columnmodel = new stdClass();
$columnmodel->attributes = array();

// cmConfig Var
include_partial('list_ajax_columnmodel_variable_cmConfig_js', array('sfExtjs2Plugin' => $sfExtjs2Plugin, 'columnmodel' => $columnmodel));

// constructor
include_partial('list_ajax_columnmodel_method_constructor_js', array('sfExtjs2Plugin' => $sfExtjs2Plugin, 'columnmodel' => $columnmodel));

// initComponent
include_partial('list_ajax_columnmodel_method_initComponent_js', array('sfExtjs2Plugin' => $sfExtjs2Plugin, 'columnmodel' => $columnmodel));

<?php
$methods =  $this->getParameterValue('columnmodel.method');
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
include_partial('<?php echo substr($method,1) ?>', array('sfExtjs2Plugin' => $sfExtjs2Plugin, 'columnmodel' => $columnmodel));
<?php
    $this->createPartialFile($method,'<?php // @object $sfExtjs2Plugin and @object $columnmodel provided ?>');
  endforeach;
endif;

$variables =  $this->getParameterValue('columnmodel.variable');
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
include_partial('<?php echo substr($variable,1) ?>', array('sfExtjs2Plugin' => $sfExtjs2Plugin, 'columnmodel' => $columnmodel));
<?php
  $this->createPartialFile($variable,'<?php // @object $sfExtjs2Plugin and @object $columnmodel provided ?>');
  endforeach;
endif;
?>

// app.sx from Symfony eXtended (instead of ux: user eXtention)
$sfExtjs2Plugin->beginClass(
  'Ext.app.sx',
  '<?php echo $cmName ?>',
  'Ext.app.sx.<?php echo $rendererName ?>',
  $columnmodel->attributes
);
$sfExtjs2Plugin->endClass();

?]
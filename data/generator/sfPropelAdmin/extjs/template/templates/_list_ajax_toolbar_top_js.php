[?php /* * Created on 20-nov-2007 * * by Leon van der Ree */ ?]
<?php


  $moduleName = ucfirst(sfInflector::camelize($this->getModuleName()));
  $toolbarName = "List".$moduleName."ToolbarTop";
  $toolbarName_xtype = "list".$this->getModuleName()."toptoolbar";
?>
[?php
$toolbar_top = new stdClass();
$toolbar_top->attributes = array();

/* top toolbar Configuration */
  $config_items = array(
    'autoWidth' => false,
    'height' => 26,
    'items' => array()
  );

// ttbConfig Var
include_partial('list_ajax_toolbar_top_variable_ttbConfig_js', array('sfExtjs2Plugin' => $sfExtjs2Plugin, 'toolbar_top' => $toolbar_top));

// constructor
include_partial('list_ajax_toolbar_top_method_constructor_js', array('sfExtjs2Plugin' => $sfExtjs2Plugin, 'toolbar_top' => $toolbar_top));

// initComponent
include_partial('list_ajax_toolbar_top_method_initComponent_js', array('sfExtjs2Plugin' => $sfExtjs2Plugin, 'toolbar_top' => $toolbar_top));

<?php
$methods =  $this->getParameterValue('toolbar_top.method');
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
include_partial('<?php echo substr($method,1) ?>', array('sfExtjs2Plugin' => $sfExtjs2Plugin, 'toolbar_top' => $toolbar_top));
<?php
    $this->createPartialFile($method,'<?php // @object $sfExtjs2Plugin and @object $toolbar_top provided ?>');
  endforeach;
endif;

$variables =  $this->getParameterValue('toolbar_top.variable');
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
include_partial('<?php echo substr($variable,1) ?>', array('sfExtjs2Plugin' => $sfExtjs2Plugin, 'toolbar_top' => $toolbar_top));
<?php
  $this->createPartialFile($variable,'<?php // @object $sfExtjs2Plugin and @object $toolbar_top provided ?>');
  endforeach;
endif;
?>

// app.sx from Symfony eXtended (instead of ux: user eXtention)
$sfExtjs2Plugin->beginClass(
  'Ext.app.sx',
  '<?php echo $toolbarName ?>',
  'Ext.Toolbar',
  $toolbar_top->attributes
);
$sfExtjs2Plugin->endClass();
?]
// register xtype
Ext.reg('<?php echo $toolbarName_xtype ?>', Ext.app.sx.<?php echo $toolbarName ?>);
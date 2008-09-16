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

$listActions = $this->getParameterValue('list.actions');
?>
// generate toolbar action handler partials
<?php
  foreach ((array) $listActions as $actionName => $params):
    if($actionName == '_fill' || $actionName == '_separator'|| $actionName == '_spacer'|| $actionName == '_text'|| isset($params['handler_function'])) continue;
    $this->createPartialFile('_list_ajax_action_'.$actionName,'<?php // @object $sfExtjs2Plugin and @object $toolbar_top provided
  $configArr["source"] = "Ext.Msg.alert(\'Error\',\'handler_function is not defined!<br><br>Edit the template \"_list_ajax_action_'.$actionName.'.php\" in your application/modules/'.strtolower($this->getModuleName()).'/templates folder and alter it or define the \"handler_function\" in your generator.yml file\');";
  $toolbar_top->attributes["'.$actionName.'"] = $sfExtjs2Plugin->asMethod($configArr);
?>');
?>
include_partial('<?php echo 'list_ajax_action_'.$actionName ?>', array('sfExtjs2Plugin' => $sfExtjs2Plugin, 'toolbar_top' => $toolbar_top));
<?php endforeach;?>

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
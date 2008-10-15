<?php
  $moduleName = ucfirst(sfInflector::camelize($this->getModuleName()));
  $rowactionsName = "List".$moduleName."RowActions";
  $rowactionsName_xtype = "list".$this->getModuleName()."rowactions";
?>
[?php
$rowactions = new stdClass();
$rowactions->attributes = array();

// ttbConfig Var
include_partial('list_ajax_rowactions_variable_rowActionsConfig_js', array('sfExtjs2Plugin' => $sfExtjs2Plugin, 'rowactions' => $rowactions));

// constructor
include_partial('list_ajax_rowactions_method_constructor_js', array('sfExtjs2Plugin' => $sfExtjs2Plugin, 'rowactions' => $rowactions));

// initComponent
include_partial('list_ajax_rowactions_method_initComponent_js', array('sfExtjs2Plugin' => $sfExtjs2Plugin, 'rowactions' => $rowactions));

<?php
$methods =  $this->getParameterValue('rowactions.method');
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
include_partial('<?php echo substr($method,1) ?>', array('sfExtjs2Plugin' => $sfExtjs2Plugin, 'rowactions' => $rowactions));
<?php
    $this->createPartialFile($method,'<?php // @object $sfExtjs2Plugin and @object $rowactions provided ?>');
  endforeach;
endif;

$variables =  $this->getParameterValue('rowactions.variable');
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
include_partial('<?php echo substr($variable,1) ?>', array('sfExtjs2Plugin' => $sfExtjs2Plugin, 'rowactions' => $rowactions));
<?php
  $this->createPartialFile($variable,'<?php // @object $sfExtjs2Plugin and @object $rowactions provided ?>');
  endforeach;
endif;

$listActions = $this->getParameterValue('list.rowactions');
?>
// generate rowactions action handler partials
<?php
  foreach ((array) $listActions as $actionName => $params):
    if(isset($params['handler_function'])) continue;?>
include_partial('<?php echo 'list_ajax_rowaction_'.$actionName ?>', array('sfExtjs2Plugin' => $sfExtjs2Plugin, 'rowactions' => $rowactions));
<?php
    if($actionName[0] == '_') continue;
    $this->createPartialFile('_list_ajax_rowaction_'.$actionName,'<?php // @object $sfExtjs2Plugin and @object $rowactions provided
  $configArr["parameters"] = "grid, record, action, row, col";
  $configArr["source"] = "Ext.Msg.alert(\'Error\',\'callback is not defined!<br><br>Copy the template file from cache \"_list_ajax_action_'.$actionName.'.php\" to your application/modules/'.strtolower($this->getModuleName()).'/templates folder and alter it or define the \"callback\" in your generator.yml file\');";
  $rowactions->attributes["'.$actionName.'"] = $sfExtjs2Plugin->asMethod($configArr);
?>');
?>
include_partial('<?php echo 'list_ajax_rowaction_'.$actionName ?>', array('sfExtjs2Plugin' => $sfExtjs2Plugin, 'rowactions' => $rowactions));
<?php endforeach;?>

// app.sx from Symfony eXtended (instead of ux: user eXtention)
$sfExtjs2Plugin->beginClass(
  'Ext.app.sx',
  '<?php echo $rowactionsName ?>',
  'Ext.ux.grid.RowActions',
  $rowactions->attributes
);
$sfExtjs2Plugin->endClass();
?]
// register xtype
Ext.reg('listopnetrowactions', Ext.app.sx.<?php echo $rowactionsName ?>);
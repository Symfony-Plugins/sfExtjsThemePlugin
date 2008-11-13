<?php
  $moduleName = ucfirst(sfInflector::camelize($this->getModuleName()));
  $className = "List".$moduleName."RowActions";
  $raConfig = $this->getRowActionsConfig();
?>
[?php
$className = '<?php echo $className ?>';
$rowactions = new stdClass();
$rowactions->attributes = array();
$config_items['actions'] = array();

<?php foreach($raConfig['actions_config'] as $configLine): ?>
$config_items['actions'][] = array(<?php echo $configLine ?>);
<?php endforeach; ?>

$rowactions->config_array = $config_items;

/* handle user credentials */
<?php echo implode("\n", $raConfig['cred_arr']) ?>

<?php
  $user_params = $this->getParameterValue('rowactions.params', array());
  if (is_array($user_params)):
?>
$rowactions->config_array = array_merge($rowactions->config_array, <?php var_export($user_params) ?>);
<?php endif; ?>
<?php echo $this->getStandardPartials('rowactions') ?>
<?php echo $this->getCustomPartials('rowactions','method'); ?>
<?php echo $this->getCustomPartials('rowactions','variable'); ?>
<?php
$listActions = $this->getParameterValue('list.rowactions');
?>
// generate rowactions action handler partials
<?php
  foreach ((array) $listActions as $actionName => $params):
    if($actionName == '_progress' || isset($params['handler_function'])) continue;?>
include_partial('<?php echo 'list_rowaction_'.$actionName ?>', array('sfExtjs2Plugin' => $sfExtjs2Plugin, 'rowactions' => $rowactions));
<?php
    if($actionName[0] == '_') continue;
    $this->createPartialFile('_list_rowaction_'.$actionName,'<?php // @object $sfExtjs2Plugin and @object $rowactions provided
  $configArr["parameters"] = "grid, record, action, row, col";
  $configArr["source"] = "Ext.Msg.alert(\'Error\',\'callback is not defined!<br><br>Copy the template file from cache \"_list_action_'.$actionName.'.php\" to your application/modules/'.strtolower($this->getModuleName()).'/templates folder and alter it or define the \"callback\" in your generator.yml file\');";
  $rowactions->attributes["'.$actionName.'"] = $sfExtjs2Plugin->asMethod($configArr);
?>');
?>
include_partial('<?php echo 'list_rowaction_'.$actionName ?>', array('sfExtjs2Plugin' => $sfExtjs2Plugin, 'rowactions' => $rowactions));
<?php endforeach;?>

// create the Ext.app.sx.<?php echo $className ?> class
$sfExtjs2Plugin->beginClass(
  'Ext.app.sx',
  '<?php echo $className ?>',
  'Ext.ux.grid.RowActions',
  $rowactions->attributes
);
$sfExtjs2Plugin->endClass();
?]
// register xtype
Ext.reg('<?php echo strtolower($className) ?>', Ext.app.sx.<?php echo $className ?>);

<?php
  $moduleName = ucfirst(sfInflector::camelize($this->getModuleName()));
  $className = "List".$moduleName."ToolbarTop";
  $xtype = "list".$this->getModuleName()."toolbartop";
  $ttbConfig = $this->getToolbarTopConfig();
?>
[?php
$className = '<?php echo $className ?>';
$toolbar_top = new stdClass();
$toolbar_top->attributes = array();
$config_items['items'] = array();

/* top toolbar Configuration */
$toolbar_top->default_config_array = array(
  'autoWidth' => false,
  'height' => 26
);

<?php foreach($ttbConfig['actions_config'] as $configLine): ?>
$config_items['items'][] = array(<?php echo $configLine ?>);
<?php endforeach; ?>

$toolbar_top->config_array = $config_items;

/* handle user credentials */
<?php echo implode("\n", $ttbConfig['cred_arr']) ?>

$toolbar_top->config_array = array_merge($toolbar_top->config_array, $toolbar_top->default_config_array);
<?php
  $user_params = $this->getParameterValue('toolbar_top.params', array());
  if (is_array($user_params)):
?>

$toolbar_top->config_array = array_merge($toolbar_top->config_array, <?php var_export($user_params) ?>);
<?php endif; ?>
<?php echo $this->getStandardPartials('toolbar_top') ?>
<?php echo $this->getCustomPartials('toolbar_top','method'); ?>
<?php echo $this->getCustomPartials('toolbar_top','variable'); ?>
<?php
$listActions = $this->getParameterValue('list.actions');
?>
// generate toolbar action handler partials
<?php
  foreach ((array) $listActions as $actionName => $params):
    if(in_array($actionName,array('_separator','_fill','_text','_spacer')) || isset($params['handler_function'])) continue;?>
include_partial('<?php echo 'list_action_'.$actionName ?>', array('sfExtjs2Plugin' => $sfExtjs2Plugin, 'toolbar_top' => $toolbar_top));
<?php
    if($actionName[0] == '_') continue;
    $this->createPartialFile('_list_action_'.$actionName,'<?php // @object $sfExtjs2Plugin and @object $toolbar_top provided
  $configArr["source"] = "Ext.Msg.alert(\'Error\',\'handler_function is not defined!<br><br>Copy the template \"_list_action_'.$actionName.'.php\" from cache to your application/modules/'.strtolower($this->getModuleName()).'/templates folder and alter it or define the \"handler_function\" in your generator.yml file\');";
  $toolbar_top->attributes["'.$actionName.'"] = $sfExtjs2Plugin->asMethod($configArr);
?>');
endforeach;?>

// create the Ext.app.sx.<?php echo $className ?> class
$sfExtjs2Plugin->beginClass(
  'Ext.app.sx',
  '<?php echo $className ?>',
  'Ext.Toolbar',
  $toolbar_top->attributes
);

$sfExtjs2Plugin->endClass();
?]
// register xtype
Ext.reg('<?php echo $xtype ?>', Ext.app.sx.<?php echo $className ?>);

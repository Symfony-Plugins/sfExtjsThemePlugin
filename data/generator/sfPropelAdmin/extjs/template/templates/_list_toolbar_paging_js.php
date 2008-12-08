<?php
  $moduleName = ucfirst(sfInflector::camelize($this->getModuleName()));
  $className = "List".$moduleName."ToolbarPaging";
  $xtype = "list".$this->getModuleName()."toolbarpaging";
  $limit = $this->getParameterValue('list.max_per_page', sfConfig::get('sf_extjs_theme_plugin_list_max_per_page', 20));
?>
[?php
$className = '<?php echo $className ?>';
$toolbar_paging = new stdClass();
$toolbar_paging->attributes = array();

/* paging toolbar configuration */
$toolbar_paging->config_array = array(
    'pageSize' => <?php echo $limit ?>,
    'displayInfo' => true,
    'displayMsg' => 'Displaying <?php echo $this->getParameterValue('object_name', $this->getModuleName()) ?>s {0} - {1} of {2}',
    'emptyMsg' => 'No <?php echo $this->getParameterValue('object_name', $this->getModuleName()) ?> to display'
);
<?php
  $user_params = $this->getParameterValue('toolbar_paging.params', array());
  if (is_array($user_params)):
?>

$toolbar_paging->config_array = array_merge($toolbar_paging->config_array, <?php var_export($user_params) ?>);
<?php endif; ?>
<?php echo $this->getStandardPartials('toolbar_paging') ?>
<?php echo $this->getCustomPartials('toolbar_paging','method'); ?>
<?php echo $this->getCustomPartials('toolbar_paging','variable'); ?>

// create the Ext.app.sx.<?php echo $className ?> class
$sfExtjs2Plugin->beginClass(
  'Ext.app.sx',
  '<?php echo $className ?>',
  'Ext.PagingToolbar',
  $toolbar_paging->attributes
);

$sfExtjs2Plugin->endClass();
?]
// register xtype
Ext.reg('<?php echo $xtype ?>', Ext.app.sx.<?php echo $className ?>);

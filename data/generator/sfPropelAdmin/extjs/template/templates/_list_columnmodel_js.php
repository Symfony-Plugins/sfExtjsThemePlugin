<?php
  $moduleName = ucfirst(sfInflector::camelize($this->getModuleName()));
  $cmConfig = $this->getColumnModelConfig();
  $className = "List".$moduleName."ColumnModel";
  $xtype = "list".$this->getModuleName()."columnmodel";
?>
[?php
$className = '<?php echo $className ?>';
$columnmodel = new stdClass();
$columnmodel->attributes = array();

$columnmodel->config_array = <?php echo preg_replace("/'(get_partial\([^\)]*\))',/", '\1,', var_export($cmConfig['col_items'], true)) ?>;

$columnmodel->plugins = <?php var_export($cmConfig['plugins']) ?>;

/* handle user credentials */
<?php echo implode("\n", $cmConfig['cred_arr']) ?>

<?php
  $user_params = $this->getParameterValue('columnmodel.params', array());
  if (is_array($user_params)):
?>
$columnmodel->config_array = array_merge($columnmodel->config_array, <?php var_export($user_params) ?>);
<?php endif; ?>
// constructor
include_partial('list_columnmodel_method_constructor_js', array('sfExtjs2Plugin' => $sfExtjs2Plugin, 'columnmodel' => $columnmodel, 'className' =>$className));

<?php echo $this->getStandardPartials('columnmodel',array('initComponent')) ?>
<?php echo $this->getCustomPartials('columnmodel','method'); ?>
<?php echo $this->getCustomPartials('columnmodel','variable'); ?>

// create the Ext.app.sx.<?php echo $className ?> class
$sfExtjs2Plugin->beginClass(
  'Ext.app.sx',
  '<?php echo $className ?>',
  'Ext.app.sx.<?php echo "List".$moduleName."Renderers" ?>',
  $columnmodel->attributes
);

$sfExtjs2Plugin->endClass();
?]
// register xtype
Ext.reg('<?php echo $xtype ?>', Ext.app.sx.<?php echo $className ?>);

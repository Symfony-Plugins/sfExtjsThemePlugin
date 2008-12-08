<?php
  $moduleName = ucfirst(sfInflector::camelize($this->getModuleName()));
  $className = "List".$moduleName."TabPanel";
  $xtype = "list".$this->getModuleName()."tabpanel";
?>
[?php
$className = '<?php echo $className ?>';
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

<?php
  $user_params = $this->getParameterValue('tabpanel.params', array());
  if (is_array($user_params)):
?>
$tabpanel->config_array = array_merge($tabpanel->config_array, <?php var_export($user_params) ?>);
<?php endif; ?>
<?php echo $this->getStandardPartials('tabpanel') ?>
<?php echo $this->getCustomPartials('tabpanel','method'); ?>
<?php echo $this->getCustomPartials('tabpanel','variable'); ?>

// create the Ext.app.sx.<?php echo $className ?> class
$sfExtjs2Plugin->beginClass(
  'Ext.app.sx',
  '<?php echo $className ?>',
  'Ext.TabPanel',
  $tabpanel->attributes
);

$sfExtjs2Plugin->endClass();
?]
// register xtype
Ext.reg('<?php echo $xtype ?>', Ext.app.sx.<?php echo $className ?>);
<?php
  $moduleName = ucfirst(sfInflector::camelize($this->getModuleName()));

  // set group field
  $group_field = $this->getParameterValue('list.grouping.field', null);
  if ($group_field)
  {
    $group_field = str_replace('/', $this->tableDelimiter, $group_field);
  }
  $store = 'Store'; if ($group_field) $store = 'Grouping'.$store;

  $className = "List".$moduleName.$store;
  $xtype = "list".$this->getModuleName().$store;
  $storeConfig = $this->getDataStoreConfig();
?>
[?php
$className = '<?php echo $className ?>';
$store = new stdClass();
$store->attributes = array();

/* Store Configuration */

// default config
$store->config_array = <?php var_export($storeConfig['options']) ?>;
$store->config_array['proxy'] = $sfExtjs2Plugin->HttpProxy(<?php var_export($storeConfig['proxy']) ?>);

$reader = <?php var_export($storeConfig['reader']) ?>;

/* handle user credentials */
<?php echo implode("\n", $storeConfig['cred_arr']) ?>

$store->config_array['reader'] = $sfExtjs2Plugin->JsonReader($reader);


<?php
  $user_params = $this->getParameterValue('datastore.params', array());
  if (is_array($user_params)):
?>
$store->config_array = array_merge($store->config_array, <?php var_export($user_params) ?>);
<?php endif; ?>
<?php echo $this->getStandardPartials('store') ?>
<?php echo $this->getCustomPartials('store','method'); ?>
<?php echo $this->getCustomPartials('store','variable'); ?>

// create the Ext.app.sx.<?php echo $className ?> class
$sfExtjs2Plugin->beginClass(
  'Ext.app.sx',
  '<?php echo $className ?>',
  'Ext.data.<?php echo $store ?>',
  $store->attributes
);

$sfExtjs2Plugin->endClass();
?]
// register xtype
Ext.reg('<?php echo $xtype ?>', Ext.app.sx.<?php echo $className ?>);

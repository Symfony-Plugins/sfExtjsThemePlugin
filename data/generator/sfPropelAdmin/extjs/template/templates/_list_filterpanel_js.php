<?php
  $moduleName = ucfirst(sfInflector::camelize($this->getModuleName()));
  $className = "List".$moduleName."FilterPanel";
  $filterConfig = $this->getFilterPanelConfig();
  $limit = $this->getParameterValue('list.max_per_page', sfConfig::get('sf_extjs_theme_plugin_list_max_per_page', 20));
?>
<?php if($this->getParameterValue('filterpanel.params.saveState')): ?>
<?php echo 'Ext.state.Manager.setProvider(new Ext.state.CookieProvider());'?>
<?php endif; ?>
[?php
$className = '<?php echo $className ?>';
$filterpanel = new stdClass();
$filterpanel->attributes = array();

/* FilterPanel Configuration */

// default config
$filterpanel->config_array = array(
  'deferredRender'      => true,
  'title'      => 'Filters',
  'autoScroll' => true,
  'bodyStyle'  => 'padding: 5px 0px 0px 10px; position: relative;',
  'labelAlign' => 'top',
  'defaults'   => array('xtype' => 'textfield', 'anchor' => '85%'),
  'items'      => <?php var_export($filterConfig['filter_config']) ?>,
  'buttons'    => array(
    $sfExtjs2Plugin->Button(array
    (
      'text'    => 'Filter',
      //TODO:  Handler needs work
      'handler' => $sfExtjs2Plugin->asMethod("
        var formpanel = (typeof this.scope != 'undefined')? this.scope : this;
        var params=formpanel.form.getValues();
        params.start=0;
        params.limit=<?php echo $limit ?>;
        formpanel.fireEvent('filter_set', params, this);
      "),
      'scope' => 'this'
    )),
    $sfExtjs2Plugin->Button(array
    (
      'text'    => 'Reset',
      //TODO:  Handler needs work
      'handler' => $sfExtjs2Plugin->asMethod("
        var formpanel = (typeof this.scope != 'undefined')? this.scope : this;
        formpanel.form.reset();
        formpanel.fireEvent('filter_reset', this);
      "),
      'scope' => 'this'
    ))
  )
);

/* handle user credentials */
<?php echo implode("\n", $filterConfig['cred_arr']) ?>

<?php
  $user_params = $this->getParameterValue('filterpanel.params', array());
  if (is_array($user_params)):
?>
$filterpanel->config_array = array_merge($filterpanel->config_array, <?php var_export($user_params) ?>);
<?php endif; ?>
<?php echo $this->getStandardPartials('filterpanel') ?>
<?php echo $this->getCustomPartials('filterpanel','method'); ?>
<?php echo $this->getCustomPartials('filterpanel','variable'); ?>

// create the Ext.app.sx.<?php echo $className ?> class
$sfExtjs2Plugin->beginClass(
  'Ext.app.sx',
  '<?php echo $className ?>',
  'Ext.FormPanel',
  $filterpanel->attributes
);

$sfExtjs2Plugin->endClass();
?]
// register xtype
Ext.reg('<?php echo strtolower($className) ?>', Ext.app.sx.<?php echo $className ?>);

[?php /* * Created on 20-nov-2007 * * by Leon van der Ree */ ?]
<?php
  $moduleName = ucfirst(sfInflector::camelize($this->getModuleName()));
  $toolbarName = "List".$moduleName."ToolbarPaging";

  $limit = $this->getParameterValue('list.max_per_page', sfConfig::get('app_sf_extjs_theme_plugin_list_max_per_page', 20));

?>
[?php
  //setup the configuration
  $config = array(
    'pageSize' => <?php echo $limit ?>,
    'displayInfo' => true,
    'displayMsg' => 'Displaying <?php echo $this->getParameterValue('object_name', $this->getModuleName()) ?>s {0} - {1} of {2}',
    'emptyMsg' => 'No <?php echo $this->getParameterValue('object_name', $this->getModuleName()) ?> to display'
  );

  // constructor
  $sfExtjs2_<?php echo $toolbarName ?>_constructor = "
    // combine <?php echo $toolbarName ?>Config with arguments
    Ext.app.sx.<?php echo $toolbarName ?>.superclass.constructor.call(this, Ext.apply(".$sfExtjs2Plugin->asAnonymousClass($config).", c));
  ";

  // initComponent
  $sfExtjs2_<?php echo $toolbarName ?>_initComponent = "
    //call parent
    Ext.app.sx.<?php echo $toolbarName ?>.superclass.initComponent.apply(this, arguments);

  ";


  // app.sx from Symfony eXtended (instead of ux: user eXtention)
  $sfExtjs2Plugin->beginClass(
    'Ext.app.sx',
    '<?php echo $toolbarName ?>',
    'Ext.PagingToolbar',
    array (
      'constructor'   => $sfExtjs2Plugin->asMethod(array(
        'parameters' => 'c',
        'source'     => $sfExtjs2_<?php echo $toolbarName ?>_constructor
      )),
      'initComponent' => $sfExtjs2Plugin->asMethod($sfExtjs2_<?php echo $toolbarName ?>_initComponent),
    )
  );
  $sfExtjs2Plugin->endClass();

?]
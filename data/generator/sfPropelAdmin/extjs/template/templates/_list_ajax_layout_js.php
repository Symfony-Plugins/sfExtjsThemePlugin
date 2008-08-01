<?php
  $moduleName = sfInflector::camelize($this->getModuleName());
  $modArr = sfConfig::get('sf_enabled_modules');
?>
[?php
  use_helper('I18N', 'Date');
?]

Ext.onReady(function(){
<?php
  // make the ajax-web-debug toolbar update itself on every ajax call
  if ((sfConfig::get('sf_environment')=='dev') && (array_search('ajaxWebdebug', $modArr) !== false)):
?>
  Ext.lib.Ajax.onStatus([200], getDebug);

  function getDebug (status, requestObj, responseObject, callback, isAbort){
    var getWebDebug = function() {
      var sfWebDebug = Ext.get('sfWebDebug');
      sfWebDebug.load({
        url : '<?php echo $this->controller->genUrl('/ajaxWebdebug/getWebdebug') ?>',
        method : 'POST'
      });
    };

    if(responseObject.options.url!='<?php echo $this->controller->genUrl('/ajaxWebdebug/getWebdebug') ?>'){
      getWebDebug();
    }
  };
<?php endif;?>


<?php $objectName = $this->getParameterValue('object_name', $this->getModuleName()) ?>
  var list<?php echo $moduleName ?>GridPanel = new Ext.app.sx.List<?php echo $moduleName ?>GridPanel();

//  list<?php echo $moduleName ?>GridPanel.on('actions', function() {alert('action: ')} );

<?php if (sfConfig::get('app_sf_extjs_theme_plugin_module_returns_layout', true)): ?>
[?php include_partial('list_ajax_viewport_js',     array('sfExtjs2Plugin' => $sfExtjs2Plugin))?]
<?php else: ?>
    <?php echo sfConfig::get('app_sf_extjs_theme_plugin_module_panel_name', 'App.RequestedModulePanel') ?> = list<?php echo $moduleName ?>GridPanel;
<?php endif; ?>

});
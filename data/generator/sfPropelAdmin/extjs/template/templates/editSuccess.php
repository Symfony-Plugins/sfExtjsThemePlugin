<?php
  $moduleName = sfInflector::camelize($this->getModuleName());
  $modArr = sfConfig::get('sf_enabled_modules');

  $groupedColumns = $this->getColumnsGrouped('edit.display', true);
  $pk = $groupedColumns['pk'];
?>
[?php use_helper('I18N', 'Date') ?]
[?php use_helper('Javascript') ?]
[?php use_helper('PJS') ?]

[?php use_pjs('<?php echo $this->getModuleName() ?>/editJs', false, array(), 'last'); ?]

  <script type="text/javascript">
    // initialise CodeLoader
    Ext.app.CodeLoader = new Ext.ux.ModuleManager({modulePath: '[?php echo $this->getContext()->getRequest()->getScriptName() ?]' });

<?php if ($use_tinymce = sfConfig::get('app_extjs2_dbfgen_theme_plugin_use_tinymce', false)): ?>
    //init TinyMCE
    Ext.ux.TinyMCE.initTinyMCE();
<?php endif ?>
  </script>

[?php
$js = sfConfig::get('extjs_default_javascripts', array());
<?php if ($use_tinymce): ?>
   $js[] = '/sfExtjsThemePlugin/js/tiny_mce/tiny_mce';
   $js[] = '/sfExtjsThemePlugin/js/ext.ux.tinymce/Ext.ux.TinyMCE.min.js';
   $js[] = '/sfExtjsThemePlugin/js/ext.ux.managediframe/miframe-min.js';
<?php endif ?>

// TODO: Need to put in a mechanism to only include extensions we are currently using in the generator.yml
$sfExtjs2Plugin = new sfExtjs2Plugin(array('theme'   => sfConfig::get('app_extjs2_dbfgen_theme_plugin_theme'),
                                           'adapter' => '<?php echo $this->getParameterValue('adapter'); ?>'),
                                     array('css' => array('/sfExtjsThemePlugin/css/symfony-extjs.css'),
                                           'js'  => $js
                                          )
                                    );
$sfExtjs2Plugin->load();
?]
<script type="text/javascript">

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
[?php
  $config = array();
  if ($<?php echo $this->getSingularName() ?>->isNew())
  {
    $config['title'] = <?php echo $this->getI18NString('edit.newtitle', 'Add '.$objectName, false); ?>;
  }
  else
  {
    $config['title'] = <?php echo $this->getI18NString('edit.title', 'Edit '.$objectName, false); ?>;
    $config['key'] = $<?php echo $this->getSingularName()?>->get<?php echo $pk->getPhpName() ?>();
  }
  $config['header'] = true; // TODO, make config option
?]

  var editPanel = new Ext.app.sx.Edit<?php echo $moduleName ?>FormPanel([?php echo $sfExtjs2Plugin->asAnonymousClass($config) ?]);
[?php if (!$<?php echo $this->getSingularName() ?>->isNew()): ?]
    editPanel.loadItem();
[?php endif; ?]

    editPanel
    editPanel.on('saved', function(panel) {alert('saved: ' + panel.getKey() )} );
    editPanel.on('deleted', function(panel) {alert('deleted: ' + panel.getKey() )} );
    editPanel.on('close_request', function(panel) {alert('Close Request: ' + panel.getKey() )} );

<?php if (sfConfig::get('app_extjs2_dbfgen_theme_plugin_module_returns_layout', true)):  ?>
    var viewport = [?php echo $sfExtjs2Plugin->Viewport(array(
      'layout'  => 'fit',
      'items'   => array($sfExtjs2Plugin->asVar('editPanel'))
    )); ?]
    viewport.doLayout();
<?php else: ?>
    <?php echo sfConfig::get('app_extjs2_dbfgen_theme_plugin_module_panel_name', 'App.RequestedModulePanel') ?> = editPanel;
<?php endif; ?>
  });

</script>

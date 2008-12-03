[?php include_partial('list_pjs_js') ?]
[?php use_helper('Javascript') ?]

  <script type="text/javascript">
    // initialise CodeLoader
    Ext.app.CodeLoader = new Ext.ux.ModuleManager({modulePath: '[?php echo $this->getContext()->getRequest()->getScriptName() ?]' });

<?php if ($use_tinymce = sfConfig::get('sf_extjs_theme_plugin_use_tinymce', false)): ?>
    //init TinyMCE
    Ext.ux.TinyMCE.initTinyMCE();
<?php endif ?>
  </script>

[?php include_partial('list_extjs2_js') ?]

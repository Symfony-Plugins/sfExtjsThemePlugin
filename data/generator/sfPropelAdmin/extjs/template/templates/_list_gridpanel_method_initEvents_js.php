[?php
// constructor
$configArr = array(
  'source' => "
    Ext.app.sx.$className.superclass.initEvents.apply(this);
    this.on({
      'afteredit' : {
        fn:     this.updateDB,
        scope:  this
      }
    });

<?php if (sfConfig::get('app_extjs2_dbfgen_theme_plugin_open_panel_handler', null)): ?>
    this.body.on({
      scope:    this,
      click:    this.onLinkClick,
      delegate: 'a.gridlink',
      stopEvent: true
    });
<?php endif; ?>
  "
);

$gridpanel->attributes['initEvents'] = $sfExtjs2Plugin->asMethod($configArr);
?]
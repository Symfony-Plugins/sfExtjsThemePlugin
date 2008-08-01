<?php
  $moduleName = ucfirst(sfInflector::camelize($this->getModuleName()));
  $panelName = "List".$moduleName."GridPanel";
?>

[?php
// constructor
$configArr = array(
  'source' => "
    Ext.app.sx.<?php echo $panelName ?>.superclass.initEvents.apply(this);
    this.on({
      'afteredit' : {
        fn:     this.updateDB,
        scope:  this
      }
    });

<?php if (sfConfig::get('app_sf_extjs_theme_plugin_open_panel_handler', null)): ?>
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

[?php
// onLinkClick
$configArr = Array(
  'parameters' => 'e, t',
  'source' => "
    // example:
    //  var el = Ext.get(e.getTarget());
    //  var modulename = el.getAttributeNS('sf_ns','modulename');
    //  var key = el.getAttributeNS('sf_ns','key');

      <?php echo sfConfig::get('app_sf_extjs_theme_plugin_open_panel_handler', 'App.openPanelHandlerMethod') ?>(e, t);
  "
);

$gridpanel->attributes['onLinkClick'] = $sfExtjs2Plugin->asMethod($configArr);
?]

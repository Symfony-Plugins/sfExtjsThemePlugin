[?php
  $panelName = "ExamplePanel";
?]
[?php
$panel_config = array(
  'xtype'               => 'panel',

  'html'                => 'this is an example panel, alter it and use it to define your own xtyped-panels',
);
?]

[?php
// initComponent
$sfExtjs2_initComponent = "
  Ext.apply(this, this.initialConfig, ".$sfExtjs2Plugin->asAnonymousClass($panel_config).");
  //call parent
  Ext.app.sx.".$panelName.".superclass.initComponent.apply(this, arguments);

//  this.addEvents(
//  );

";

// app.sx from Symfony eXtended (instead of ux: user eXtention)
$sfExtjs2Plugin->beginClass(
  'Ext.app.sx',
  $panelName,
  'Ext.Panel',
  array (
    'initComponent' => $sfExtjs2Plugin->asMethod($sfExtjs2_initComponent),
  )
);
$sfExtjs2Plugin->endClass();

?]
// register xtype
Ext.reg('[?php echo strtolower($panelName) ?]', Ext.app.sx.[?php echo $panelName ?]);

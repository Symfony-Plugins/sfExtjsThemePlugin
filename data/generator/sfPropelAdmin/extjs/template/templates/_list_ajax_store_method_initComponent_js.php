[?php
// constructor
$configArr['source'] = "Ext.app.sx.$panelName.superclass.initComponent.apply(this, arguments);";
$store->attributes['initComponent'] = $sfExtjs2Plugin->asMethod($configArr);
?]

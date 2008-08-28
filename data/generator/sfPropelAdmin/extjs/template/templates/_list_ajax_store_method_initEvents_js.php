[?php
// constructor
$configArr['source'] = "Ext.app.sx.$panelName.superclass.initEvents.apply(this);";
$store->attributes['initEvents'] = $sfExtjs2Plugin->asMethod($configArr);
?]

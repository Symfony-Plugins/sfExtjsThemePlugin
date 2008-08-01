<?php
  $moduleName = ucfirst(sfInflector::camelize($this->getModuleName()));
  $panelName = "List".$moduleName."GridPanel";
?>

[?php
// onRender
$configArr = array(
  'parameters' => 'ct, position',
  'source' => "
    Ext.app.sx.<?php echo $panelName ?>.superclass.onRender.apply(this, arguments);
    if(this.autoLoadStore) this.store.load();
  "
);

$gridpanel->attributes['onRender'] = $sfExtjs2Plugin->asMethod($configArr);
?]

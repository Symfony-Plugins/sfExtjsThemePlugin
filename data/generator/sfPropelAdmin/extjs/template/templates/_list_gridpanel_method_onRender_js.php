[?php
// onRender
$configArr = array(
  'parameters' => 'ct, position',
  'source' => "
    Ext.app.sx.$className.superclass.onRender.apply(this, arguments);
<?php if(!$this->getParameterValue('filterpanel.params.saveState')): ?>
    if(this.autoLoadStore) this.store.load();
<?php endif; ?>
  "
);

$gridpanel->attributes['onRender'] = $sfExtjs2Plugin->asMethod($configArr);
?]
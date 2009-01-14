<?php $objectName = $this->getParameterValue('object_name', $this->getModuleName()) ?>
[?php
// onLinkClick
$configArr = Array(
  'parameters' => 'e, t',
  'source' => "
    var el = Ext.get(e.getTarget());
    var id = el.getAttributeNS('sf_ns','key');
    if(!this.ownerCt.findById(id)){
      this.ownerCt.add({
        xtype: 'edit<?php echo $this->getModuleName() ?>formpanel',
        id: id,
        title: 'Edit <?php echo $objectName ?> '+id,
        key: id,
        bodyStyle: 'padding: 10px 0px 10px 0px;',
      }).show()
    } else {
      this.ownerCt.setActiveTab(this.ownerCt.findById(id));
    }
  "
);

$gridpanel->attributes['onLinkClick'] = $sfExtjs2Plugin->asMethod($configArr);
?]
<?php $moduleName = sfInflector::camelize($this->getModuleName()) ?>
[?php // @object $sfExtjs2Plugin and @object $toolbar_top provided
  $configArr["source"] = "
  var edit<?php echo $moduleName ?>FormPanel = Ext.ComponentMgr.create({
    xtype : 'edit<?php echo $this->getModuleName() ?>formpanel'
  });

  var tabpanel = this.ownerCt.ownerCt;
  tabpanel.add(edit<?php echo $moduleName ?>FormPanel);
  tabpanel.activate(edit<?php echo $moduleName ?>FormPanel);

  edit<?php echo $moduleName ?>FormPanel.on('close_request', function(){
    //reload the gridpanel
    tabpanel.getComponent(0).store.reload();
    tabpanel.remove(this);});
  ";
  $toolbar_top->attributes["_create"] = $sfExtjs2Plugin->asMethod($configArr);
?]
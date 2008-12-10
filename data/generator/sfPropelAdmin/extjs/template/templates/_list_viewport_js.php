<?php $moduleName = sfInflector::camelize($this->getModuleName()) ?>
  var list<?php echo $moduleName ?>TabPanel = Ext.ComponentMgr.create({
    xtype : 'list<?php echo $this->getModuleName() ?>tabpanel',
    items : [list<?php echo $moduleName ?>GridPanel]
  });

  var list<?php echo $moduleName ?>ViewPort = Ext.ComponentMgr.create({
    xtype : 'viewport',
    layout: 'fit',
    items : [list<?php echo $moduleName ?>TabPanel]
  });

  list<?php echo $moduleName ?>ViewPort.doLayout();
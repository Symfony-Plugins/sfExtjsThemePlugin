[?php
// constructor
$configArr = array(
  'source' => "
Ext.app.sx.$className.superclass.initEvents.apply(this);
this.on({
  'afteredit' : {
    fn:     this.updateDB,
    scope:  this
  }
});

<?php if (sfConfig::get('sf_extjs_theme_plugin_open_panel_handler', null)): ?>
this.body.on({
  scope:    this,
  click:    this.onLinkClick,
  delegate: 'a.gridlink',
  stopEvent: true
});
<?php endif; ?>

<?php if (is_array($this->getParameterValue('edit.display'))): ?>
<?php $objectName = $this->getParameterValue('object_name', $this->getModuleName()) ?>
this.on({
  'celldblclick' : {
    fn: function(grid, rowIndex, columnIndex, e){
      if(columnIndex == 0) {
        var record = grid.getStore().getAt(rowIndex);
        if(!this.ownerCt.findById(record.id)){
          var formpanel = Ext.ComponentMgr.create({
            xtype: 'editlbrequestconfigformpanel',
            id: record.id,
            title: 'Edit <?php echo $objectName ?> '+record.id,
            key: record.id,
          });
          this.ownerCt.add(formpanel).show()
          formpanel.on('close_request', function(){
            this.ownerCt.getComponent(0).store.reload();
            this.ownerCt.remove(this);
          });
        } else {
          this.ownerCt.setActiveTab(this.ownerCt.findById(record.id));
        }
      }
    },
    scope:  this
  }
});
<?php endif; ?>
  "
);

$gridpanel->attributes['initEvents'] = $sfExtjs2Plugin->asMethod($configArr);
?]
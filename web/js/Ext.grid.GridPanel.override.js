Ext.override(Ext.grid.GridPanel, {
  initComponent : Ext.grid.GridPanel.prototype.initComponent.createSequence(function()
  {
    if (this.colModel)
    {
      this.colModel.grid = this;
    }
  })
});
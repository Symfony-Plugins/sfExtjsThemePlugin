// allows button to extend BoxComponent
Ext.ux.BoxButton = function(config)
{
  Ext.Button.call(this, config);
};
Ext.apply(Ext.ux.BoxButton.prototype, Ext.BoxComponent.prototype);
Ext.apply(Ext.ux.BoxButton.prototype, Ext.Button.prototype);
Ext.reg('button', Ext.ux.BoxButton);

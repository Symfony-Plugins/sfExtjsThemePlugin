Ext.namespace('Ext.ux.form');

Ext.ux.form.ComboBox = function(config) {
  if (Ext.isArray(config.store)) {
    if (Ext.isArray(config.store[0])) {
      config.store = new Ext.data.SimpleStore({
        fields : ['value', 'text'],
        data : config.store
      });
      config.valueField = 'value';
      config.displayField = 'text';
    } else {
      var store = [];
      for (var i = 0, len = config.store.length; i < len; i++)
        store[i] = [config.store[i]];
      config.store = new Ext.data.SimpleStore({
        fields : ['text'],
        data : store
      });
      config.valueField = 'text';
      config.displayField = 'text';
    }
    config.mode = 'local';
  }
  Ext.ux.form.ComboBox.superclass.constructor.call(this, config);
}

Ext.extend(Ext.ux.form.ComboBox, Ext.form.ComboBox, {});
Ext.reg('combo', Ext.ux.form.ComboBox);
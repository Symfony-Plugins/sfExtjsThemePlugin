// override to provie getValue and setValue functions for radio groups

Ext.override(Ext.form.RadioGroup, {
  getValue : function()
  {
    return this.items.first().getGroupValue();
  },
  setValue : function(v)
  {
    this.items.first().setValue(v);
  }
});
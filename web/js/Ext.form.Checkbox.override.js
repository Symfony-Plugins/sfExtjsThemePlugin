// IE alignment fixes

Ext.override(Ext.form.Checkbox, {
  getResizeEl : function()
  {
    if (!this.resizeEl)
    {
      this.resizeEl = Ext.isSafari || Ext.isIE ? this.wrap : (this.wrap.up('.x-form-element', 5) || this.wrap);
    }
    return this.resizeEl;
  },

  // private
  initEvents : function()
  {
    Ext.form.Checkbox.superclass.initEvents.call(this);
    this.addEvents('reset');
    this.initCheckEvents();
  },

  reset : function()
  {
    this.fireEvent('reset', this);
  }
});
// IE alignment fixes

Ext.override(Ext.form.Checkbox, {
  getResizeEl : function()
  {
    if (!this.resizeEl)
    {
      this.resizeEl = Ext.isSafari || Ext.isIE ? this.wrap : (this.wrap.up('.x-form-element', 5) || this.wrap);
    }
    return this.resizeEl;
  }
});
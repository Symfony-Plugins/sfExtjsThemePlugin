// adds the ability to show help icon with tooltip for form fields

Ext.override(Ext.form.Field, {
  // private
  afterRender : function()
  {
    if (this.helpText)
    {
      // radio
      if (this.inputType == 'radio')
      {
        var p = this.getEl().up('div.x-form-radio-wrap');
        var style = 'margin-left:-1px; padding: 1px 0px 0px 12px;'
      }
      // checkbox
      else if ('undefined' != typeof this.checked)
      {
        var p = this.getEl().up('div.x-form-check-wrap-inner').child('label');
        var style = 'margin-left:-2px; padding: 1px 0px 0px 12px;'
      }
      // everything else
      else
      {
        var p = this.getEl().up('div.x-form-element');
        var style = 'margin-left:2px; padding: 2px 0px 1px 12px;'
      }
      if (p)
      {
        var helpImage = p.createChild({
          tag : 'div',
          cls : Ext.ux.IconMgr.getIcon('help'),
          style : 'cursor: help; background-color: transparent;background-repeat: no-repeat; display: inline;' + style,
          html : '&nbsp;'
        });

        Ext.QuickTips.register({
          target : helpImage,
          title : '',
          text : this.helpText,
          enabled : true
        });
      }
    }
    Ext.form.Field.superclass.afterRender.call(this);
    this.initEvents();
    this.initValue();
  }
});

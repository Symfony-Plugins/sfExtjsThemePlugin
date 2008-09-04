// allows button to extend BoxComponent
Ext.override(Ext.BoxComponent, {
  adjustSize : function(w, h)
  {
    if (this.autoWidth === true)
    {
      w = 'auto';
    }
    if (this.autoHeight === true)
    {
      h = 'auto';
    }
    return {
      width : w,
      height : h
    };
  }
});

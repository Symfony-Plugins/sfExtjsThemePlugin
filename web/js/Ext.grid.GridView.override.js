//gets rid of the handle when everything is headers are disabled in a grid
Ext.override(Ext.grid.GridView, {
  handleHdMove : function(e, t) {
    if (this.activeHd && !this.headersDisabled && (this.grid.enableColumnResize !== false)) {
      var hw = this.splitHandleWidth || 5;
      var r = this.activeHdRegion;
      var x = e.getPageX();
      var ss = this.activeHd.style;
      if (x - r.left <= hw && this.cm.isResizable(this.activeHdIndex - 1)) {
        if (Ext.isSafari) {
          ss.cursor = 'e-resize';
        } else {
          ss.cursor = 'col-resize';
        }
      } else if (r.right - x <= (!this.activeHdBtn ? hw : 2) && this.cm.isResizable(this.activeHdIndex)) {
        if (Ext.isSafari) {
          ss.cursor = 'w-resize';
        } else {
          ss.cursor = 'col-resize';
        }
      } else {
        ss.cursor = '';
      }
    }
  }
});
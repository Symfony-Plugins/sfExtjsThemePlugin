Ext.namespace('Ext.ux.grid');
Ext.ux.grid.CheckColumn = function(config) {


  Ext.apply(this, config);
  if (!this.id) {
    this.id = Ext.id();
  }
  //default to editable false
  this.editable = (typeof this.editable != 'undefined') ? this.editable : 'true' ;
  this.renderer = (typeof this.renderer != 'undefined') ? this.renderer.createDelegate(this) : this.checkboxRenderer.createDelegate(this);
};

Ext.extend(Ext.ux.grid.CheckColumn, Ext.util.Observable, {
  init : function(grid) {
    this.grid = grid;
    this.grid.on('render', function() {
      var view = this.grid.getView();
      view.mainBody.on('mousedown', this.onMouseDown, this);
    }, this, {single: true});
  },

  onMouseDown : function(e, t) {
    if (t.className && t.className.indexOf('x-grid3-cc-' + this.id) != -1 && this.editable) {
      e.stopEvent();
      var index = this.grid.getView().findRowIndex(t);
      var record = this.grid.store.getAt(index);
      record.set(this.dataIndex, !record.data[this.dataIndex]);
      this.grid.fireEvent('afterEdit', {
        grid : this.grid,
        record : record,
        field : this.dataIndex,
        value : record.data[this.dataIndex],
        originalValue : !record.data[this.dataIndex],
        row : index,
        column : this.grid.getColumnModel().getIndexById(this.id)
      });
    }
  },

  checkboxRenderer : function(v, p, record) {
    p.css += ' x-grid3-check-col-td';
    return '<div class="x-grid3-check-col' + (v ? '-on' : '') + ' x-grid3-cc-' + this.id + '">&#160;</div>';
  }
});

Ext.reg('checkcolumn', Ext.ux.grid.CheckColumn);
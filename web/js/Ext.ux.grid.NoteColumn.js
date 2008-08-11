Ext.namespace('Ext.ux.grid');
Ext.ux.grid.NoteColumn = function(config) {
  Ext.apply(this, config);
  if (!this.id) {
    this.id = Ext.id();
  }
  this.renderer = this.renderer.createDelegate(this);
};

Ext.extend(Ext.ux.grid.NoteColumn, Ext.util.Observable, {
  init : function(grid) {
    this.grid = grid;
    this.grid.on('render', function() {
      var view = this.grid.getView();
      view.mainBody.on('mousedown', this.onMouseDown, this);
    }, this);
  },

  onMouseDown : function(e, t) {
    if (t.className && t.className.indexOf('x-grid3-cc-' + this.id) != -1) {
      e.stopEvent();
      var index = this.grid.getView().findRowIndex(t);
      var note = new Ext.ux.NoteWindow({
        notesUrl: this.notesUrl,
        notesUpdateUrl: this.notesUpdateUrl,
        relatedStore:  this.grid.store,
        id: index
      });
      note.show(document.body);
    }
  },

  renderer : function(v, p, record) {
    return v;
  }
});

Ext.reg('notecolumn', Ext.ux.grid.NoteColumn);
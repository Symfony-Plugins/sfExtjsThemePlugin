Ext.namespace('Ext.ux.grid');
Ext.ux.grid.NoteColumn = function(config)
{
  Ext.apply(this, config);
  if (!this.id)
  {
    this.id = Ext.id();
    this.width = 80;
    this.sortable = false;
  }
  this.renderer = this.renderer.createDelegate(this);
};

Ext.extend(Ext.ux.grid.NoteColumn, Ext.util.Observable, {
  init : function(grid)
  {
    this.grid = grid;
    this.grid.on('render', function()
    {
      var view = this.grid.getView();
      view.mainBody.on('dblclick', this.onDblClick, this);
    }, this);
  },

  onDblClick : function(e, t)
  {
    if (t.className && t.className.indexOf('x-grid3-nc-' + this.id) != -1)
    {
      e.stopEvent();
      var index = this.grid.getView().findRowIndex(t);
      var record = this.grid.store.getAt(index);
      if (!Ext.app.note)
      {
        Ext.app.note = new Ext.ux.NoteWindow({
          notesUrl : this.notesUrl,
          notesUpdateUrl : this.notesUpdateUrl,
          relatedStore : this.grid.store,
          id : record.data.id
        });
      }
      else
      {
        Ext.app.note.noteStore.load({
          params : {
            id : record.data.id
          }
        });
        Ext.app.note.editFormPanel.form.baseParams = {
          id : record.data.id
        };
      }
      Ext.app.note.show(t.parentNode.parentNode);
    }
  },

  renderer : function(v, c, r)
  {
    if (Ext.util.CSS.getRule('.note-l') == null)
    {
      var styleBody =
        '.note-l {background: transparent url(/sfExtjsThemePlugin/Ext.ux.NoteWindow/images/comment.gif) no-repeat left;}'
          + '.note-r {text-align: right;line-height: 16px !important;}';

      var styleSheet =
        Ext.util.CSS.createStyleSheet('/* Ext.ux.form.NoteColumn stylesheet */\n' + styleBody, 'NoteColumn');
      Ext.util.CSS.refreshCache();
    }

    if (!v)
      return '<p class="x-grid3-note-col x-grid3-nc-' + this.id + '">None</div>';
    c.css = c.css + "note-r";
    return String.format('<p ext:qtitle="{0} Wrote:" ext:qtip=\'{1}\' class="note-l  x-grid3-nc-' + this.id
      + '">{0}</p>', v, r.data['last_comment']);
  }
});

Ext.reg('notecolumn', Ext.ux.grid.NoteColumn);
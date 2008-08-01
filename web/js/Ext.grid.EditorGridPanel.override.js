// two tiny changes to make grids allow staying in a field
Ext.grid.EditorGridPanel.override({
  // prevent getting focus if activeEditor is not giving up his focus
  startEditing : Ext.grid.EditorGridPanel.prototype.startEditing.createInterceptor(function(row, col) {
    if (this.stopEditing() === false) {
      this.activeEditor.field.focus();
      return false;
    }
  }),

  /**
   * Stops any active editing
   *
   * @param {Boolean}
   *          cancel (optional) True to cancel any changes
   */
  stopEditing : function(cancel) {
    if (this.activeEditor) {
      this.activeEditor[cancel === true ? 'cancelEdit' : 'completeEdit']();
      if (this.activeEditor.editing === true)
        return false;
    }
    this.activeEditor = null;
  }
});

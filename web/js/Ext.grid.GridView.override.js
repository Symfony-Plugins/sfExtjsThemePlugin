//adds support for autoExpandColumn and forceFit to be used at the same time
Ext.override(Ext.grid.GridView, {
  fitColumns : function(preventRefresh, onlyExpand, omitColumn)
  {
    if (this.grid.autoExpandColumn)
    {
      // First display
      if (omitColumn == null)
      {
        this.autoExpand(preventRefresh);
      }

      // Report optional overflow to autoExpandColumn (and remove last virtual
      // column)
      var autoExpandColIndex = this.cm.getIndexById(this.grid.autoExpandColumn);
      var overflow = this.cm.getTotalWidth(false) - this.grid.getGridEl().getWidth(true);

      this.cm.setColumnWidth(autoExpandColIndex, this.cm.getColumnWidth(autoExpandColIndex) - overflow, true);

      if (preventRefresh !== true)
      {
        this.updateAllColumnWidths();
      }
    }
    else
    {
      this.fitCol(preventRefresh, onlyExpand, omitColumn);
    }
  },

  fitCol : function(preventRefresh, onlyExpand, omitColumn)
  {
    var cm = this.cm, leftOver, dist, i;
    var tw = cm.getTotalWidth(false);
    var aw = this.grid.getGridEl().getWidth(true) - this.scrollOffset;

    if (aw < 20)
    { // not initialized, so don't screw up the default widths
      return;
    }
    var extra = aw - tw;

    if (extra === 0)
    {
      return false;
    }

    var vc = cm.getColumnCount(true);
    var ac = vc - (typeof omitColumn == 'number' ? 1 : 0);
    if (ac === 0)
    {
      ac = 1;
      omitColumn = undefined;
    }
    var colCount = cm.getColumnCount();
    var cols = [];
    var extraCol = 0;
    var width = 0;
    var w;
    for (i = 0; i < colCount; i++)
    {
      if (!cm.isHidden(i) && !cm.isFixed(i) && i !== omitColumn)
      {
        w = cm.getColumnWidth(i);
        cols.push(i);
        extraCol = i;
        cols.push(w);
        width += w;
      }
    }
    var frac = (aw - cm.getTotalWidth()) / width;
    while (cols.length)
    {
      w = cols.pop();
      i = cols.pop();
      cm.setColumnWidth(i, Math.max(this.grid.minColumnWidth, Math.floor(w + w * frac)), true);
    }

    if ((tw = cm.getTotalWidth(false)) > aw)
    {
      var adjustCol = ac != vc ? omitColumn : extraCol;
      cm.setColumnWidth(adjustCol, Math.max(1, cm.getColumnWidth(adjustCol) - (tw - aw)), true);
    }

    if (preventRefresh !== true)
    {
      this.updateAllColumnWidths();
    }

    return true;
  }
});
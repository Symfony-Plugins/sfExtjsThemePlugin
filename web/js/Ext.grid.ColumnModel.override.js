// adding posibility to accept both columns and editors with/from xtype

Ext.grid.ColumnModel.override({
  /**
   * Default editable of columns which do not have the "editable" config
   * specified (defaults to false)
   *
   * @cfg {Boolean/Function} defaultEditable true if the column is editable, or
   *      a function which returns true/false when passed the arguments: grid,
   *      row index, column index (defaults to false). If set to true, a custom
   *      {@link #getCellEditor} method (which returns a default Editor instance
   *      for columns without Editors) must be specified.
   */
  defaultEditable : false,

  /**
   * Returns true if the cell is editable.
   *
   * @param {Number}
   *          colIndex The column index
   * @param {Number}
   *          rowIndex The row index
   * @param {Ext.grid.GridPanel}
   *          The {@link Ext.grid.GridPanel} which owns this ColumnModel
   * @return {Boolean}
   */
  isCellEditable : function(colIndex, rowIndex)
  {
    var col = this.config[colIndex], ok =
      col.editable != undefined ? col.editable : col.editor != undefined ? true : this.defaultEditable;

    return ok === true || (typeof ok == 'function' && ok(this.grid, rowIndex, colIndex));
  },

  /**
   * Sets if a column is editable.
   *
   * @param {Number}
   *          col The column index
   * @param {Boolean/Function}
   *          editable true if the column is editable, or a function which
   *          returns true/false when passed the arguments: grid, row index,
   *          column index.
   */
  setEditable : function(col, editable)
  {
    this.config[col].editable = editable;
  },

  // adds in proper disabling of the header menu when enableHdMenu is set to
  // false
  isMenuDisabled : function(col)
  {
    return ('undefined' != typeof this.grid) ? !this.grid.enableHdMenu : true || !!this.config[col].menuDisabled;
  },
  /**
   * Reconfigures this column model, also accepting xtypes:
   * http://extjs.com/forum/showthread.php?p=180237
   *
   * @param {Array}
   *          config Array of Column configs
   */
  setConfig : function(config, initial)
  {
    if (!initial)
    { // cleanup
      delete this.totalWidth;
      for (var i = 0, len = this.config.length; i < len; i++)
      {
        var c = this.config[i];
        if (c.editor)
        {
          c.editor.destroy();
        }
      }
    }
    // pre-process all columns with xtypes
    for (var i = 0, len = config.length; i < len; i++)
    {
      if (config[i].xtype)
      { // create column-objects
        config[i] = Ext.ComponentMgr.create(config[i]);
      }
    }
    this.config = config;
    this.lookup = {};
    // if no id, create one
    for (var i = 0, len = config.length; i < len; i++)
    {
      var c = config[i];
      if (typeof c.renderer == "string")
      {
        c.renderer = Ext.util.Format[c.renderer];
      }
      if (typeof c.id == "undefined")
      {
        c.id = i;
      }
      if (c.editor)
      {
        // if not already an Ext.grid.GridEditor
        if (!c.editor.field)
        {
          // if it isn't an Ext.form.Field
          if (!c.editor.isFormField)
          {
            c.editor = Ext.ComponentMgr.create(c.editor, 'textfield'); // get
            // instance
            // from
            // xtype
            // config,
            // default
            // to
            // textfield
          }
          // create gridEditor
          c.editor = new Ext.grid.GridEditor(c.editor);
          // listen to events to off preloading
          c.editor.on('beforestartedit', function(ed, boundEl, v)
          {
            if (typeof this.preload == 'function')
            {
              this.preload(v, boundEl.dom.textContent);
            }
          }, c.editor.field);
          // listen to events to prevent leaving focus when in invalid state
          c.editor.on('beforecomplete', function()
          {
            if (typeof this.allowLeave == 'function')
            {
              return this.allowLeave();
            }
          }, c.editor.field);
        }
      }
      this.lookup[c.id] = c;
    }
    if (!initial)
    {
      this.fireEvent('configchange', this);
    }
  },

  getSortField : function(col)
  {
    return (this.config[col].sortField) ? this.config[col].sortField : this.config[col].dataIndex;
  },

  /**
   * Finds the index of the first matching column for the given dataIndex.
   *
   * @param {String}
   *          col The dataIndex to find
   * @return {Number} The column index, or -1 if no match was found
   */
  findSortIndex : function(dataIndex)
  {
    var c = this.config;

    for (var i = 0, len = c.length; i < len; i++)
    {
      var index = (c[i].sortField) ? c[i].sortField : c[i].dataIndex;
      if (index == dataIndex)
      {
        return i;
      }
    }
    return -1;
  }
});

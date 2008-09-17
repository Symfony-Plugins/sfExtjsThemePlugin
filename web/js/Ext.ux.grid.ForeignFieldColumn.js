// By KRavEN and LvanderRee
// set up foreign-key-column with combobox, dynamic+static comob-store,
// gridfield-renderer and preloading objects from grid-store
Ext.namespace("Ext.ux.renderer");
Ext.ux.renderer.ComboRenderer = function(options)
{
  var value = options.value;
  var combo = options.combo;

  if (value === '')
    return; // skip if no value (foreign-key) defined

  var returnValue = 'Ext.ux.renderer problem'; // initial value, replaced below
  // (if everything goes well)

  // get returnValue from comboBox-store
  var idx = combo.store.findBy(function(record)
  {
    if (record.get(combo.valueField) == value)
    {
      returnValue = record.get(combo.displayField);
      return true;
    }
  });
  // if combos-store does not contain the display-value, try grid-store
  // preloaded field
  // FOR THIS TO WORK GRID needs to be up to date
  if (idx == -1)
  {
    idx = options.gridStore.findBy(function(record)
    {
      if (record.get(combo.dataIndex) == value)
      {
        returnValue = record.get(combo.preloadedField);

        if (typeof combo.preload == 'function')
        {
          combo.preload(value, returnValue);
        }
        return true;
      }
    });
  }

  return returnValue;
};

Ext.namespace('Ext.ux.grid');
Ext.ux.grid.ForeignFieldColumn = function(config)
{
  Ext.apply(this, config);

  var comboConfig = {
    xtype : 'comboboxautoload',

    url : this.url,
    root : this.root,

    valueField : this.valueField,
    displayField : this.displayField,

    store : this.store,

    queryParam : this.queryParam,
    sortField : this.sortField,

    dataIndex : this.dataIndex,
    preloadedField : this.preloadedField,
    relatedTableName : this.relatedTableName,
    relatedModuleName : this.relatedModuleName,
    relatedFieldName : this.relatedFieldName
  };

  // config.comboConfig should override anything set in comboConfig
  Ext.apply(comboConfig, config.comboConfig);
  this.editor = Ext.ComponentMgr.create(comboConfig);;
  this.renderer = this.renderer(this.editor);

  if (config.store)
  {
    this.triggerAction = 'all';
    this.hiddenName = this.name;
  }

  Ext.ux.grid.ForeignFieldColumn.superclass.constructor.call(this);
};

Ext.extend(Ext.ux.grid.ForeignFieldColumn, Ext.form.ComboBox, {

  initEvents : function()
  {
    Ext.ux.grid.ForeignFieldColumn.superclass.initEvents.call(this);

    if (this.filter)
    {
      this.on('select', function()
      {
        if (this.value)
        {
          this.ownerCt.buttons[0].handler()
        }
      }, this);
    }
  },

  reset : function()
  {
    return this.clearValue();
  },

  renderer : function(combo)
  {
    // in some cases this is required, especially if the displayField is an
    // aggregate function and not a column
    if (this.editor.store != undefined && combo.preLoad)
    {
      combo.pageSize = 0;
      combo.editable = false;
      combo.mode = 'local';
      this.editor.store.load();
    }

    return function(value, meta, record, rowIndex, colIndex, store)
    {
      return Ext.ux.renderer.ComboRenderer({
        value : value,
        meta : meta,
        record : record,
        combo : combo,
        gridStore : store
      });
    };
  }
});
Ext.reg('foreignfieldcolumn', Ext.ux.grid.ForeignFieldColumn);

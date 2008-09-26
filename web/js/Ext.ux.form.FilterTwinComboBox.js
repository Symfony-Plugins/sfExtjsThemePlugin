Ext.namespace("Ext.ux.form");
Ext.ux.form.FilterTwinComboBox = Ext.extend(Ext.form.ComboBox, {
  constructor : function(config)
  {
    // config should override defaults
    this.lazyRender = true;
    this.triggerAction = 'all';
    this.editable = true;
    this.chained = null;
    this.forceSelection = false;
    this.selectOnFocus = true;
    this.typeAhead = true;
    this.minChars = 2;
    this.pageSize = 20;
    this.queryParam = config.name;

    Ext.apply(this, config);

    // change minimum width of drop-down to 150 in case of paging
    if ((this.pageSize != 0) && (!this.minListWidth))
    {
      this.minListWidth = 150;
    }

    this.hiddenName = this.name;

    if (!this.store)
    {
      this.store = new Ext.data.Store({
        baseParams : {
          filter : this.chained
        },
        proxy : new Ext.data.HttpProxy({
          url : this.url,
          method : 'POST'
        }),
        reader : new Ext.data.JsonReader({
          id : this.valueField,
          root : this.root || 'data',
          totalProperty : 'totalCount'
        }, [{
          name : this.valueField
        }, {
          name : this.displayField
        }]),
        sortInfo : {
          field : this.sortField,
          direction : 'asc'
        },
        remoteSort : true
      });
    }

    if ('undefined' != typeof this.groupField)
      this.store.baseParams.group = this.groupField;

    // config.comboConfig should override anything set in this
    Ext.apply(this, this.comboConfig);

    Ext.ux.form.FilterTwinComboBox.superclass.constructor.call(this);
  },

  applyState : function(state)
  {
    this.setValue(state.selectedIndex);
  },

  getState : function()
  {
    return {
      selectedIndex : this.getValue()
    };
  },

  initEvents : function()
  {
    Ext.ux.form.FilterTwinComboBox.superclass.initEvents.call(this);

    this.on('select', function()
    {
      if (this.value)
        this.ownerCt.buttons[0].handler();
    }, this);
    if (this.chained == 'query')
    {
      this.on('blur', function()
      {
        this.lastQuery = null;
      }, this);
    }
  },

  initComponent : Ext.form.TwinTriggerField.prototype.initComponent,
  getTrigger : Ext.form.TwinTriggerField.prototype.getTrigger,
  initTrigger : Ext.form.TwinTriggerField.prototype.initTrigger,
  trigger1Class : 'x-form-clear-trigger',
  hideTrigger1 : true,

  onRender : function(ct, position)
  {
    Ext.ux.form.FilterTwinComboBox.superclass.onRender.call(this, ct, position);
    if(this.getValue())this.triggers[0].show();
  },

  reset : Ext.form.Field.prototype.reset.createSequence(function()
  {
    this.clearValue();
    this.triggers[0].hide();
    this.fireEvent('select', this);
  }),

  onViewClick : Ext.form.ComboBox.prototype.onViewClick.createSequence(function()
  {
    this.triggers[0].show(); // Added to show trigger
  }),

  onTrigger2Click : function()
  {
    this.onTriggerClick();
  },

  onTrigger1Click : function()
  {
    this.clearValue();
    this.triggers[0].hide();
    this.fireEvent('clear', this);
    this.ownerCt.buttons[0].handler();
  }
});
Ext.reg('filtertwincombobox', Ext.ux.form.FilterTwinComboBox);
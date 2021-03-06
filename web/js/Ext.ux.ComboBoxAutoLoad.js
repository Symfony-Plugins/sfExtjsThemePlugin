Ext.namespace('Ext.ux');

/**
 * @class Ext.ux.ComboBoxAutoLoad
 * @extends Ext.form.ComboBox A ComboBox that automatically preloads the store
 *          with content from grid/form and displays its first entry. also
 *          capable of showing pop-up windows to add new items thanks to
 *          daspunkt - http://extjs.com/forum/showthread.php?t=21113 altered to
 *          make the make the comboBox autoLoad even more
 */
Ext.ux.ComboBoxAutoLoad = function(config)
{
  // config should override defaults
  this.lazyRender = (typeof config.lazyRender != 'undefined') ? config.lazyRender : true;
  this.triggerAction = config.triggerAction || 'all';
  this.editable = (typeof config.editable != 'undefined') ? config.editable : true;
  this.chained = (typeof config.chained != 'undefined') ? config.chained : 'query';
  this.forceSelection = config.forceSelection || false;
  this.selectOnFocus = (typeof config.selectOnFocus != 'undefined') ? config.selectOnFocus : true;
  this.typeAhead = (typeof config.typeAhead != 'undefined') ? config.typeAhead : true;
  this.minChars = config.minChars || 2;
  this.mode = config.mode || 'remote';
  this.pageSize = config.pageSize || 20;

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

  if('undefined' != typeof this.groupField) this.store.baseParams.group = this.groupField;

  // config.comboConfig should override anything set in this
  Ext.apply(this, this.comboConfig);

  Ext.ux.ComboBoxAutoLoad.superclass.constructor.call(this);

};

Ext.extend(Ext.ux.ComboBoxAutoLoad, Ext.form.ComboBox, {

  initEvents : function()
  {
    Ext.ux.ComboBoxAutoLoad.superclass.initEvents.call(this);

    if (this.filter)
    {
      this.on('select', function()
      {
        if (this.value)
        {
          this.filterpanel.buttons[0].handler()
        }
      }, this);
    }
    // this.on('expand', function(){console.log(this.store.load())});
    // this.on('collapse', function(){console.log('collapse')});
  },

  updateBoxLabel : function()
  {
    // only add boxLabel in form, not in grid or filter
    if (this.ownerCt && !this.filter)
    {
      if ((typeof this.value != 'undefined') && (this.value !== ''))
      {
        this.boxLabel = '<a href="#" class="showEditor">Modify</a>';
      }
      else
      {
        this.boxLabel = '<a href="#" class="showEditor">Add new</a>';
      }
      if (this.boxLabel)
      {
        if (this.BoxLabelElement)
          this.BoxLabelElement.remove();
        this.BoxLabelElement = this.wrap.createChild({
          tag : 'label',
          htmlFor : this.el.id,
          cls : 'x-form-combo-label',
          html : this.boxLabel
        });
        this.BoxLabelElement.on({
          click : this.showEditor,
          delegate : 'a.showEditor',
          stopEvent : true,
          scope : this
        });
      }
    }
  },

  // preloads a key-value pair; adding it into the combo-datastore
  preload : function(i, value)
  {

    if ((this.store !== null) && (i != ""))
    {
      // test if value can be found in combostore, if not add preloaded value
      // from grid/form store.
      if (this.store.find(this.valueField, i) == -1)
      {
        // add preloaded value to combo-datastore
        var o = new Array();
        o.data = new Array();
        o.data[0] = new Array();
        o.data[0][this.valueField] = i;
        o.data[0][this.displayField] = value;

        // console.log('preload: '+ i + ' => ' + value);
        this.store.loadData(o, true);

        return true;
      };
    }
  },

  // this method contains logic for checking if you are allowed to leave the
  // field
  // if field contains new/invalid value you are not, else you are
  allowLeave : function()
  {
    // console.log('allowLeave with ' + this.getRawValue() + '?');

    // you are allowed to leave the field when it is empty or when cancelling of
    // course, or when you drop-it down
    if ((this.getRawValue() != "") && (this.canceling !== true) && (!this.isExpanded()))
    {

      // don't leave when this field contains a new value
      // TODO: store needs to be loaded for this..., should be done with a
      // callback or something
      if (this.store.find(this.displayField, this.getRawValue()) == -1)
      {
        this.showEditor();
        return false;
      }
    }

    return true
  },

  forceQuery : function()
  {
    this.lastQuery = undefined;
    this.doQuery(this.getRawValue(), true);
  },

  onRender : function(ct, position)
  {
    Ext.ux.ComboBoxAutoLoad.superclass.onRender.call(this, ct, position);
    this.updateBoxLabel();
    if (this.filter)
      this.filterpanel = this.findParentBy(function(p)
      {
        return p.title == 'Filters'
      });
  },

  /**
   * Sets the specified value into the field. If the value finds a match, the
   * corresponding record text will be displayed in the field. If the value does
   * not match the data value of an existing item, and the valueNotFoundText
   * config option is defined, it will be displayed as the default field text.
   * Otherwise the field will be blank (although the value will still be set).
   *
   * @param {String}
   *          value The value to match
   */
  setValue : function(v)
  {
    var text = v;
    if (this.valueField)
    {
      var r = this.findRecord(this.valueField, v);
      if (r)
      {
        text = r.data[this.displayField];
        if (!this.filter)
          this.valueNotFoundText = text;
      }
      else if (this.valueNotFoundText !== undefined)
      {
        text = this.valueNotFoundText;
        // restore add it to combostore
        if (!this.filter)
          this.preload(v, text);
      }
    }

    this.lastSelectionText = text;
    if (this.hiddenField)
    {
      this.hiddenField.value = v;
    }

    // combo super, not this super
    Ext.form.ComboBox.superclass.setValue.call(this, text);
    this.value = v;

    this.updateBoxLabel();
  },

  // private
  onFocus : function()
  {
    // if in form (instead of grid)
    if (this.ownerCt && !this.filter)
    {
      // console.log(this.name+'.onFocus()');

      var ae = this.ownerCt.activeEditor;
      if ((ae !== null) && (ae !== undefined) && (ae !== this))
      {
        var ok = ae.allowLeave();
        if (ok === false)
        {
          // ae.focus();
          return false; // stop onFocus, because we want to be in another field.
        }
      }
      this.ownerCt.activeEditor = this; // this is the current activeEditor of
      // this form
    }

    Ext.ux.ComboBoxAutoLoad.superclass.onFocus.call(this);
  },

  // private
  onBlur : function()
  {
    if (this.el.dom.value === '')
    {
      delete this.value;
    }

    this.updateBoxLabel();

    // if in form (instead of grid)
    if (this.ownerCt && !this.filter)
    {
      // console.log(this.name+'.onBlur()');

      var ae = this.ownerCt.activeEditor;
      if (ae !== this)
      {
        return true; // stop onBlur, because we should be in another field.
      }

      var ok = this.allowLeave();

      if (ok === false)
      { // field is not valid
        // this.focus();
        return false; // stop onBlur, because we want to stay in this field
      }
      else
      {
        this.ownerCt.activeEditor = null; // allowed to leave
      }
    }

    Ext.ux.ComboBoxAutoLoad.superclass.onBlur.call(this);
  },

  // controlFilterOnSelect : function(combo, record, index)
  // {
  // this.filterpanel = this.findParentBy(function(p){return
  // p.title=='Filters'});
  // },

  showEditor : function()
  {
    var fields = new Array();
    // using('url(' + App.baseUrl + '/js/' + this.relatedModuleName +
    // '/editAjaxJs.pjs)', function() {

    var editPanel = {
      xtype : ('Edit' + this.relatedModuleName + 'FormPanel').toLowerCase(),
      hideBorders : true,
      header : false,
      key : this.value
    };
    // create an instance from its xtype
    editPanel = Ext.ComponentMgr.create(editPanel);
    // init items in panel
    editPanel.initItems();

    // get field in form with same name as display-value of combo
    var field = editPanel.items.find(function(i)
    {
      return (i.name == this.relatedTableName + '[' + this.relatedFieldName + ']')
    }, this);
    // when modifying existing item load
    if ((typeof this.value != 'undefined') && (this.value !== ''))
    {
      editPanel.loadItem();
    }
    else
    {
      // set text-value in field
      if (field)
        field.setValue(this.getRawValue()); // TODO: maybe also set
      // originalValue for form.reset() to
      // work
    }
    // set focus to field
    if (field)
    {
      editPanel.on('afterlayout', function()
      {
        this.focus(true, 100);
      }, field);
    }

    var editWindow = new Ext.Window({
      title : 'Loading...',
      modal : true,
      items : editPanel,
      editPanel : editPanel
    });

    editWindow.on({
      'close' : {
        fn : function()
        {
          this.editWindow.destroy();
          // return focus to field
          this.field.focus();
        },
        scope : {
          field : this,
          editWindow : editWindow
        }
      }
    });

    editPanel.on({
      'afterlayout' : {
        fn : function()
        {
          var height = this.editPanel.body.dom.scrollHeight; // +
          // this.body.getFrameWidth('tb');
          this.editPanel.body.setHeight(height);

          var width = this.editPanel.body.dom.scrollWidth; // +
          // this.body.getFrameWidth('tb');
          this.setWidth(width);

          this.setTitle(this.editPanel.title);
        },
        scope : editWindow
      },

      'titlechange' : {
        fn : function(p, title)
        {
          this.setTitle(title);
        },
        scope : editWindow
      },

      'close_request' : {
        fn : function()
        {

          editWindow.close();
        },
        scope : this
      },
      'saved' : {
        fn : function()
        {
          // TODO set to added item (get ID, and field), next Field focus
          editWindow.close();
          this.forceQuery(); // this can be done from info of window
        },
        scope : this
      }
    });

    editWindow.show();
    // }, this);
  }

});
Ext.ComponentMgr.registerType("comboboxautoload", Ext.ux.ComboBoxAutoLoad);

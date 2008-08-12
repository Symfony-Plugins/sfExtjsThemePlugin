Ext.ux.NoteWindow = Ext.extend(Ext.Window, {
  submitNote : function()
  {
    this.editFormPanel.form.submit({
      waitMsg : 'Please Wait...',
      success : function()
      {
        this.hide();
        this.relatedStore.load();
      },
      scope : this
    });
  },
  constructor : function(c)
  {
    this.noteStore = new Ext.data.Store({
      proxy : new Ext.data.HttpProxy({
        url : c.notesUrl,
        method : 'POST'
      }),
      reader : new Ext.data.JsonReader({
        root : 'notes'
      }, [{
        name : 'user'
      }, {
        name : 'date'
      }, {
        name : 'comment'
      }, {
        name : 'username'
      }])
    });
    this.noteTpl =
      new Ext.XTemplate('<tpl for=".">' + '<div class="commentblock">' + '<div class="cheader">'
        + '<div class="cuser" ext:qtip="{username}">{user}:</div>' + '<div class="commentmetadata">{date}</div>'
        + '</div>' + '<span>{comment}</span>' + '</div>' + '</tpl>');

    this.noteStore.load({params:{id: c.id}});

    this.editFormPanel = new Ext.form.FormPanel({
      method : 'POST',
      url : c.notesUpdateUrl,
      waitMsg : 'Loading Data...',
      baseParams : {
        id : c.id
      },
      autoHeight : true,
      bodyStyle : 'padding:10px 10px 5px 10px;',
      hideBorders : true,
      items : [{
        xtype : 'fieldset',
        autoHeight : true,
        title : 'Notes',
        items : [{
          xtype: 'htmleditor',
          hideLabel : true,
          name : 'note',
          enableFont : false,
          enableSourceEdit : false,
          enableAlignments : false,
          enableLists : false,
          width : 364
        }, {
          xtype : 'panel',
          bodyStyle : 'margin-top:5px;margin-bottom:5px;',
          frame : false,
          autoScroll : true,
          height : 280,
          layout : 'fit',
          items : new Ext.DataView({
            autoWidth : true,
            tpl : this.noteTpl,
            store : this.noteStore,
            itemSelector : '.cheader'
          })
        }]
      }]
    });

    Ext.ux.NoteWindow.superclass.constructor.call(this, Ext.applyIf(c || {}, {
      title : 'Turnover Notes',
      buttonAlign : 'center',
      closable : true,
      bodyStyle : 'padding-bottom:0px;',
      width : 420,
      height : 600,
      border : false,
      plain : true,
      // layout : 'fit',
      proxyDrag : false,
      resizable : false,
      modal : true,
      closeAction : 'hide',
      items : this.editFormPanel,
      buttons : [{
        text : 'Submit',
        handler : this.submitNote.createDelegate(this)
      }]
    }, c));

    if(Ext.util.CSS.getRule('.cheader')==null)
    {
      var styleBody =
      '.note-l {background: transparent url(/sfExtjsThemePlugin/Ext.ux.NoteWindow/images/comment.gif) no-repeat left;}'
        + '.note-r {text-align: right;line-height: 16px !important;}'
        + '.cheader {height: 20px;}'
        + '.cuser {background: transparent url(/sfExtjsThemePlugin/Ext.ux.NoteWindow/images/comment.gif) no-repeat scroll 0% 0%; clear: none; float: left; font-family: tahoma, arial; font-size: 12px; font-style: normal; font-variant: normal; font-weight: bold; line-height: normal; padding-left: 21px; padding-top: 2px;}'
        + '.commentmetadata {clear: none; color: gray; display: block; float: right; font-size: 9px; font-weight: normal; margin: 0 0 10px;}'
        + '.commentblock {background: transparent url(/sfExtjsThemePlugin/Ext.ux.NoteWindow/images/comment-bg.gif) repeat-x scroll 0 -5px; color: #333333; display: block; font-size: 11px; padding: 10px 10px 15px;}';

      var styleSheet = Ext.util.CSS.createStyleSheet('/* Ext.ux.NoteWindow stylesheet */\n'+styleBody, 'NoteWindow');
      Ext.util.CSS.refreshCache();
    }

    this.on('hide', function(){
      this.editFormPanel.findBy(function(f){return f.name=='note' })[0].reset();
    }, this)
  }
});;

Ext.reg('notewindow', Ext.ux.NoteWindow);

/*note = new Ext.ux.NoteWindow({
  notesUrl: 'lbrequest/noteJsonList',
  notesUpdateUrl: 'lbrequest/noteAjaxUpdate',
  relatedStore:  ticketTabs.getComponent(0).store,
  id: 1
});
note.show(document.body);*/

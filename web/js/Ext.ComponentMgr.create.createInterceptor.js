Ext.ComponentMgr.create = Ext.ComponentMgr.create.createInterceptor(function(config, defaultType){
  var xtype = config.xtype || defaultType;
  if (!this.hasType(xtype)){

    Ext.app.CodeLoader.load(
      Ext.MessageBox.wait("Loading Panel", "Please Wait..."),
      {async:false, method:'POST', cacheResponses:true},
      '/js/getXtype/' + xtype
    );

    Ext.MessageBox.hide()

  }
});
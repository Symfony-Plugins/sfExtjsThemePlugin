Ext.ComponentMgr.create = Ext.ComponentMgr.create.createInterceptor(function(config, defaultType){
  var xtype = config.xtype || defaultType;
  if (!this.hasType(xtype)){
    // show a Load Message
    var loadMessage = Ext.MessageBox.wait("Loading Panel", "Please Wait...");

    Ext.app.CodeLoader.load( {async:false, method:'GET', cacheResponses:true}, '/js/getXtype/' + xtype );

    loadMessage.hide();
  }
});
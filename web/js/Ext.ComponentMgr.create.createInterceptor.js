Ext.ComponentMgr.create = Ext.ComponentMgr.create.createInterceptor(function(config, defaultType){
  var xtype = config.xtype || defaultType;
  if (!this.hasType(xtype)){
    // initialise LoadMask
    var loadMask = new Ext.LoadMask(Ext.getBody(), {msg:"<b>Loading Panel</b><br>&nbsp;<br>please wait..."});
    loadMask.show();

    Ext.app.CodeLoader.load( {async:false, method:'GET', cacheResponses:true}, '/js/getXtype/' + xtype );

    loadMask.hide();
  }
});
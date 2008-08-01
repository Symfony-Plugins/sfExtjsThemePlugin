Ext.ux.IconMgr.setIconPath('/sfExtjsThemePlugin/Ext.ux.IconMgr');

// TODO: this has to move to its own file probably
//this won't work for panels loaded after onReader ....
//Ext.onReady(function()
//{
//  Ext.app.IconMgrCreate();
//});

//this probably is quite heavy... but didn't found a nicer way yet!
//Ext.Container.prototype.render = Ext.Container.prototype.render.createSequence(function(){
//});

//OBSOLETE This can be removed, both below and above.
//// TODO: this should probably move to its own file, and it shouldn't process the entire dom, but accept the panel.el as root-node (however this seems to fail)
//Ext.app.IconMgrCreate = function(el) {
//    if (typeof el === 'undefined') el = document;
//
//    var create_classes = Ext.query('*[class*=\"IconMgrCreate_\"]', el);//this.getEl()
//    Ext.each(create_classes, function(e){
//      var el = Ext.get(e);
//      var classes = el.getAttributeNS(null, 'class');
//      if (classes) {
//        var classes = classes.split(' ');
//        Ext.each(classes, function(c){
//          if (c.substr(0,14) == 'IconMgrCreate_') {
//            var iconCls = c.substr(14);
//            el.replaceClass(c, Ext.ux.IconMgr.getIcon(iconCls));
//            return false;
//          }
//        });
//      }
//    });
//};

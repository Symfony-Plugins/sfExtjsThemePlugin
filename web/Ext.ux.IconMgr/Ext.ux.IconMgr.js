/*
 * Author : Jay Garcia Site : http://tdg-i.com Contact Info : jgarcia@tdg-i.com
 * Purpose : CSS set using the famfamfam silk icon set. Icon Sources :
 * http://www.famfamfam.com/lab/icons/silk/ : Warranty : none Price : free
 */
Ext.ns('Ext.ux');
var x;
Ext.ux.IconMgr = function(iconName)
{
  var cssClasses = new Ext.data.JsonStore({
    autoLoad : false,
    fields : [{
      name : 'name',
      mapping : 'name'
    }, {
      name : 'cssRule',
      mapping : 'cssRule'
    }, {
      name : 'styleBody',
      mapping : 'styleBody'
    }]
  });

  x = cssClasses;
  var styleSheetId = 'IconMgr';
  var styleSheet;

  var ruleBodyTpl = ' \n\r .{0} {  background-image: url({1}) !important; }';

  return {
    getIcon : function(icon)
    {
      var cls = String('iconmgr_' + Ext.id()).replace('-', '_');
      var extension = (icon.substring(icon.length-4,icon.length-3)!='.') ? this.imgExtension :'';
      var iconImgPath = this.iconPath + '/icons/' + icon + extension;
      var styleBody = String.format(ruleBodyTpl, cls, iconImgPath);

      var foundIcon = cssClasses.findBy(function(rec, ind)
      {
        if (rec.data.name == icon)
        {
          return (ind);
        }
      });

      if (foundIcon < 0)
      {
        cssClasses.add(new Ext.data.Record({
          name : icon,
          cssRule : cls,
          styleTxt : styleBody
        }));
        var styleSheet = Ext.get(styleSheetId);

        if (!Ext.isIE)
        {
          styleSheet.dom.sheet.insertRule(styleBody, styleSheet.dom.sheet.cssRules.length);
        }
        else
        {
          // Per http://www.quirksmode.org/dom/w3c_css.html#properties
          document.styleSheets[styleSheetId].cssText += styleBody;
        }
        Ext.util.CSS.refreshCache();

        return (cls);
      }
      else
      {
        return (cssClasses.getAt(foundIcon).data.cssRule);
      }

    },

    setIconPath : function(path)
    {
      this.iconPath = path || '';
      this.styleSheetNum = document.styleSheets.length;
      styleSheet = Ext.util.CSS.createStyleSheet('/* Ext.ux.IconMgr stylesheet */\n', styleSheetId);
      (Ext.isIE6) ? this.imgExtension = '.gif' : this.imgExtension = '.png';
    }
  }
}();

Ext.ux.IconBrowser = function()
{
  var win;
  var view;
  var imgsFile;
  var grid;

  return {
    imgRenderer : function(val)
    {
      return ('<img src="' + this.dataFilePath + '/' + val + '">');
      consol
    },
    init : function()
    {
      if (!win)
      {
        (Ext.isIE6) ? imgsFile = this.dataFilePath + '/icons_gif.js' : imgsFile = this.dataFilePath + '/icons_png.js';
        grid = new Ext.grid.GridPanel({
          autoExpandColumn : 'imgName',
          store : new Ext.data.JsonStore({
            url : imgsFile,
            autoLoad : true,
            root : 'icons',
            fields : [{
              name : 'name',
              mapping : 'name'
            }]
          }),
          columns : [{
            header : '',
            dataIndex : 'name',
            renderer : this.imgRenderer.createDelegate(this),
            width : 30
          }, {
            header : 'Image File Name',
            id : 'imgName',
            dataIndex : 'name'
          }]

        });

        win = new Ext.Window({
          height : 350,
          width : 350,
          layout : 'fit',
          closeAction : 'hide',
          title : 'Icon browser',
          items : [grid],
          buttons : [{
            text : 'OK',
            handler : this.hide,
            scope : this
          }]

        });
      }

    },
    hide : function()
    {
      win.hide();
    },
    show : function()
    {
      this.init();
      if (!this.dataFilePath)
      {
        Ext.MessageBox
            .alert('Error',
                   'Please set the path! E.g. Ext.ux.IconMgr.setPath("/sfExtjsThemePlugin/Ext.ux.IconMgr").show();');
        return;
      }
      win.show();
    },
    setPath : function(path)
    {
      this.dataFilePath = path;
      return (this);
    }
  }
}();

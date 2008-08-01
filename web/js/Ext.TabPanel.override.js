Ext.namespace('Ext.ux');

/**
 * @class Ext.TabPanel
 * added functionality to set activeTab to none (item=-1)
 */
Ext.TabPanel.override({
    setActiveTab : function(item){
        if (item != -1){
          item = this.getComponent(item);
          if(!item || this.fireEvent('beforetabchange', this, item, this.activeTab) === false){
              return;
          }
        }
        if(!this.rendered){
            this.activeTab = item;
            return;
        }
        if(this.activeTab != item){
            if(this.activeTab){
                var oldEl = this.getTabEl(this.activeTab);
                if(oldEl){
                    Ext.fly(oldEl).removeClass('x-tab-strip-active');
                }
                this.activeTab.fireEvent('deactivate', this.activeTab);
            }
            if (item != -1){
              var el = this.getTabEl(item);
              Ext.fly(el).addClass('x-tab-strip-active');
              this.activeTab = item;
              this.stack.add(item);

              this.layout.setActiveItem(item);
              if(this.layoutOnTabChange && item.doLayout){
                  item.doLayout();
              }
              if(this.scrolling){
                  this.scrollToTab(item, this.animScroll);
              }
              item.fireEvent('activate', item);
              this.fireEvent('tabchange', this, item);

            } else { // non-active
              this.activeTab = null;
            }
        }
    }
 });

 Ext.menu.Menu.override({
    /**
     * Removes an {@link Ext.menu.Item} from the menu and destroys the object
     * @param {Ext.menu.Item} item The menu item to remove
     */
    remove : function(item){
        this.items.removeKey(item.id);
        //item.container.remove();
        item.destroy();
    }
});

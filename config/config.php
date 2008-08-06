<?php

// Route to xtype-script-getter
sfRouting::getInstance()->prependRoute('sf_extjs_theme_plugin_get_xtype',
  '/js/getXtype/:xtype/*.js',
  array(
    'module' => 'sfExtjsThemePluginXtypeManager',
    'action' => 'find'
  )
);


$quoteExcept = array(
  'key' => array('renderer', 'store'),
  'value' => array('true', 'false', 'new Ext.', 'function', 'Ext.')
);

sfConfig::set('extjs_quote_except', $quoteExcept);

$default_javascripts = array(
  '/sfExtjsThemePlugin/js/Ext.form.BasicForm.override.js',    // allow preloading combo-items
  '/sfExtjsThemePlugin/js/Ext.grid.ColumnModel.override.js',  // adds construction from xtype (for columns and their editors) and sets event-handlers
  '/sfExtjsThemePlugin/js/Ext.grid.EditorGridPanel.override.js',  // grid.editor and form.field changes to detect leaving field and throw extra events
  '/sfExtjsThemePlugin/js/Ext.menu.Menu.override.js',         // adds possibility to remove items from menu
  '/sfExtjsThemePlugin/js/Ext.TabPanel.override.js',          // added functionality to set activeTab to none (item=-1)
  '/sfExtjsThemePlugin/js/Ext.grid.GridView.override.js',     // fixes to have no trigger when enableHdMenu: false
  '/sfExtjsThemePlugin/js/Ext.layout.FormLayout.override.js', // fixes for form labels

  '/sfExtjsThemePlugin/Ext.ux.IconMgr/Ext.ux.IconMgr.js',  // icon manager extension
  '/sfExtjsThemePlugin/js/ext-basex/ext-basex-min.js',            // BaseX-3.0 library, used for monitoring XHR requests (monitoring credentials) and lazy loading
  '/sfExtjsThemePlugin/js/Ext.ComponentMgr.create.createInterceptor.js',  // Interceptor for create method to lazy-load xtypes, REQUIRES INITIALISATION!

  '/sfExtjsThemePlugin/js/Ext.ux.form.ComboBox.js',           // allows comboboxes to take an array for the store

  '/sfExtjsThemePlugin/js/Ext.ux.TabCloseMenu.js',            // simple context menu for closing tabs or multiple tabs
  '/sfExtjsThemePlugin/js/Ext.ux.grid.CheckColumn.js',        // lets you set a column to show a checkbox
  '/sfExtjsThemePlugin/js/Ext.ux.grid.GroupSummary.js',
  '/sfExtjsThemePlugin/js/Ext.ux.grid.RowExpander.js',
  '/sfExtjsThemePlugin/js/Ext.ux.grid.RowAutoExpander.js',
  '/sfExtjsThemePlugin/js/Ext.ux.ComboBoxAutoLoad.js',        // auto fill combo-store with preloaded values from grid/form and capable of adding new items by calling pop-up window

  '/sfExtjsThemePlugin/js/Ext.ux.grid.ForeignFieldColumn.js', // grid-column which knows how to handle data from related- les (sets renderers, and combos)

// not needed anymore due to ExtJS2.2 and Basex override for serializeForm (data += this.encoder(name) + '=' + this.encoder(el.checked ? true : false) + '&';)
// also extra css has been added for X-Checkbox left-align in symfony-extjs.css
//  '/sfExtjsThemePlugin/Ext.ux.form.Checkbox/Ext.ux.form.Checkbox.js',  //makes checkboxes look and behave correctly (or at least that was the idea...)

  '/sfExtjsThemePlugin/Ext.ux.UploadDialog/Ext.ux.UploadDialog.js',  // an uploadDialog
  '/sfExtjsThemePlugin/js/extConstants.js',                   // set some constants in here

);

sfConfig::set('extjs_default_javascripts', $default_javascripts);

<?php if (sfConfig::get('app_sf_extjs_theme_plugin_list_tabbed',true)): ?>

    //tabPanel contain the grid panel
    tabPanel = new Ext.TabPanel({
      deferredRender: false,
      renderTo:'tabs',
      tabWidth:135,
      enableTabScroll:true,
      plugins: new Ext.ux.TabCloseMenu(),
      activeTab: 0,
      layoutOnTabChange: true,      
      items: [gridPanel
<?php if ($this->getParameterValue('list.filters') && sfConfig::get('app_sf_extjs_theme_plugin_filter_add_handler', null) == null): ?>
      , formPanel
<?php endif; ?>
      ]
    });

<?php else: ?>
    tabPanel = new Ext.Panel({
      renderTo:'tabs',
      layout: 'card',
      autoWidth:true,
      autoHeight:true,
      defaults: {
        autoScroll: true
      },
      activeItem: 0,
      items: [gridPanel]
    });
<?php endif; ?>
<?php
    if ($this->getParameterValue('list.filters') && sfConfig::get('app_sf_extjs_theme_plugin_filter_add_handler', null) != null)
       echo sfConfig::get('app_sf_extjs_theme_plugin_filter_add_handler', null).'(formPanel);';
    ?>
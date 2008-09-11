<?php
  $moduleName = sfInflector::camelize($this->getModuleName());
  $list_ns = ucfirst(sfInflector::camelize($this->getModuleName()))."List";

?>
  if(typeof closeable =='undefined') var closeable = true;
  if(typeof active =='undefined') var active = true;
  if(typeof icon   =='undefined') var icon = 'icon-edit';
  if(typeof params =='undefined') var params = {};

  if (!(tab = tabPanel.items.find(function(i){return i.key === tabId;}))) {
    using('url(<?php echo $this->controller->genUrl('js/'.$this->getModuleName()) ?>/editAjaxJs.pjs)', function() {
      var tab = new Ext.app.sx.Edit<?php echo $moduleName ?>Panel([?php echo $sfExtjs2Plugin->asAnonymousClass(array(
        'height'      => 400, //deze aan
//        'autoHeight'  => true,
        'title'       => $sfExtjs2Plugin->asVar('tabTitle'),
        'iconCls'     => $sfExtjs2Plugin->asVar('icon'),
        'closable'    => $sfExtjs2Plugin->asVar('closeable'),
        'hideBorders' => true,
        'key'         => $sfExtjs2Plugin->asVar('tabId')
      ))?]);

      tab.on('saved',         function(ep, old_key) { <?php echo $list_ns ?>.getDataStore().reload(); } );
      tab.on('deleted',       function(ep)          { <?php echo $list_ns ?>.getDataStore().reload(); tabPanel.remove(ep); } );
      tab.on('close_request', function(ep)          { tabPanel.remove(ep); } );

      tabPanel.add(tab);
      if(active) {
        tabPanel.setActiveTab(tab);
        tabPanel.doLayout();
      }
      tab.loadItem();
    });
  } else {
    if(active) {
      tabPanel.setActiveTab(tab);
      tabPanel.doLayout();
    }
  }

<?php

class BasesfExtjsThemePluginXtypeManagerActions extends sfActions
{
  public function executeFind()
  {
    $xtype = $this->getRequestParameter('xtype');
    $this->forward404Unless($xtype);

    $module = null;
    $action = null;
    $url = null;

    switch (substr($xtype,0,4))
    {
      case 'list':
        if (substr($xtype,-9) == 'gridpanel')
        {
          $module = substr($xtype,4,-9);
          $action = 'listAjaxGridPanelJs';
        }

        if ($module) $url = 'js/'.$module.'/listAjaxGridPanelJs.pjs';
        break;

      case 'edit':
        if (substr($xtype,-9) == 'formpanel')
        {
          $module = substr($xtype,4,-9);
          $action = 'editAjaxJs';
        }
        elseif (substr($xtype,-5) == 'panel')
        {
          $module = substr($xtype,4,-5);
          $action = 'editAjaxJs';
        }

        if ($module) $url = 'js/'.$module.'/editAjaxJs.pjs';
        break;
    }

    // Change view to sfJavascript
    $this->getContext()->getRequest()->setAttribute($module.'_'.$action.'_view_name', 'sfJavascript', 'symfony/action/view');

    // Forward to the "real" javascript action
    $this->forward($module, $action);
  }
}

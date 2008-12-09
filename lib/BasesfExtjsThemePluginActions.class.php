<?php
class BasesfExtjsThemePluginActions extends sfActions
{
  /**
   * PJS Include methods
   */
  public function executeEditJs()
  {

  }

  public function executeListJs()
  {

  }

  public function executeListGridPanelJs()
  {

  }

  public function executeListFilterPanelJs()
  {

  }

  public function executeListTabPanelJs()
  {

  }

  public function executeListRowActionsJs()
  {

  }

  public function executeJsonResponse()
  {
    $json = $this->getRequestParameter('json',false);
    if(!$json)
    {
      $json_success = $this->getRequestParameter('json_success',true);
      $jsonArr = array('success' => $json_success);

      if(!$json_success)
      {
        $errorArr = array();
        //TODO: enhance the javascript to handle/display multiple errors
        foreach($this->getRequest()->getErrors() as $error => $errorText)
        {
          $errorArr[] = array(
            'error'   => ucfirst($error),
            'message' => $errorText
          );
        }
        $jsonArr['errors'] = $errorArr;
      }

      $json = json_encode($jsonArr);
    }
    sfConfig::set('sf_web_debug', false);
    return $this->renderText($json);
  }

  /**
   * Shortcut method for sfRequest::setParameter
   */
  protected function setRequestParameter($name, $value, $ns=null)
  {
    return $this->getRequest()->setParameter($name, $value, $ns);
  }
}

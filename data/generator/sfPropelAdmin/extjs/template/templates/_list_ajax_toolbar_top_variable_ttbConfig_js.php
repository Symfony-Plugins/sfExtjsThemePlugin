<?php
$moduleName = ucfirst(sfInflector::camelize($this->getModuleName()));
$toolbarName = "List".$moduleName."ToolbarTop";

$listActions = $this->getParameterValue('list.actions');
if (null === $listActions)
{
  $listActions = array(
    '_create' => array(),
    '_refresh' => array()
  );
}

$credArr = array();
$i=0;?>
[?php
<?php foreach ((array) $listActions as $actionName => $params):
//handle credentials for displaying the button
  $buttoncreds = (isset($params['credentials']))?$params['credentials']:false;
  if ($buttoncreds)
  {
    $buttoncreds = str_replace("\n", ' ', var_export($buttoncreds, true));
    //if the user doesn't have the right permissions remove the columnconfig
    $credArr[] = 'if(!$sf_user->hasCredential('.$buttoncreds.')) unset($toolbar_top->config_array[\'items\']['.$i.']);';
  }
?>
  $config_items['items'][] = array(<?php echo $this->getAjaxButtonToToolbarAction($actionName, $params, false) ?>);
<?php $i++; endforeach; ?>

  $toolbar_top->config_array = $config_items;
/* handle user credentials */
<?php echo implode("\n", $credArr) ?>
?]

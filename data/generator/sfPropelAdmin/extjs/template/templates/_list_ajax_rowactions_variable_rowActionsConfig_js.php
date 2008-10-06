<?php
$listRowactions = $this->getParameterValue('list.rowactions');

$credArr = array();
$i=0;?>
[?php
$config_items['actions'] = array();
<?php foreach ((array) $listRowactions as $actionName => $params):
//handle credentials for displaying the action
  $actioncreds = (isset($params['credentials']))?$params['credentials']:false;
  if ($actioncreds)
  {
    $actioncreds = str_replace("\n", ' ', var_export($actioncreds, true));
    //if the user doesn't have the right permissions remove the button config
    $credArr[] = 'if(!$sf_user->hasCredential('.$actioncreds.')) unset($rowactions->config_array[\'items\']['.$i.']);';
  }
?>
  $config_items['actions'][] = array(<?php echo $this->getAjaxRowAction($actionName, $params) ?>);
<?php $i++; endforeach; ?>

  $rowactions->config_array = $config_items;
/* handle user credentials */
<?php echo implode("\n", $credArr) ?>

<?php
  $user_params = $this->getParameterValue('rowactions.params', array());
  if (is_array($user_params)):
?>
$rowactions->config_array = array_merge($rowactions->config_array, <?php var_export($user_params) ?>);
<?php endif; ?>

?]

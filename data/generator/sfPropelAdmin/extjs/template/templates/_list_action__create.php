[?php // @object $sfExtjs2Plugin and @object $toolbar_top provided
<?php
  $handler_function = "window.location = '".$this->controller->genUrl($this->getModuleName().'/create')."'";
  if ($openPanelFunction = sfConfig::get('sf_extjs_theme_plugin_open_panel', null))
  {
    $handler_function = $openPanelFunction."('".strtolower($this->getModuleName())."')";
  }

  if ($gridListCreateLink = sfConfig::get('sf_extjs_theme_plugin_list_action_handler', null))
  {
    $handler_function = $gridListCreateLink."('".$this->controller->genUrl($this->getModuleName().'/create')."', null,  '".$default_name."')";
  }
?>
  $configArr["source"] = "<?php echo  $handler_function ?>";
  $toolbar_top->attributes["_create"] = $sfExtjs2Plugin->asMethod($configArr);
?]
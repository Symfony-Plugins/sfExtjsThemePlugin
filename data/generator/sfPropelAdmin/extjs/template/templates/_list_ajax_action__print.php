[?php // @object $sfExtjs2Plugin and @object $toolbar_top provided
  $configArr["source"] = "window.open('".$this->controller->genUrl($this->getModuleName().'/listPrint')."')";
  $toolbar_top->attributes["_delete"] = $sfExtjs2Plugin->asMethod($configArr);
?]
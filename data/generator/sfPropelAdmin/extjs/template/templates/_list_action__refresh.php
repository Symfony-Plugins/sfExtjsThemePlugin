[?php // @object $sfExtjs2Plugin and @object $toolbar_top provided
  $configArr["source"] = "this.store.reload();";
  $toolbar_top->attributes["_refresh"] = $sfExtjs2Plugin->asMethod($configArr);
?]
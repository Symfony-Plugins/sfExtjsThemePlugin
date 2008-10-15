[?php // @object $sfExtjs2Plugin and @object $rowactions provided
  $configArr["parameters"] = "grid, record, action, row, col";
  $configArr["source"] = "console.log(grid)";
  $rowactions->attributes["_delete"] = $sfExtjs2Plugin->asMethod($configArr);
?]
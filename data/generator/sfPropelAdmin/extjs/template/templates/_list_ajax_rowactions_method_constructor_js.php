<?php
  $moduleName = ucfirst(sfInflector::camelize($this->getModuleName()));
  $rowactionsName = "List".$moduleName."RowActions";
?>

[?php
// constructor

$configArr['parameters'] = 'c';
$configArr['source'] = "
    //rowactions config
    this.rowactionsConfig = ".$sfExtjs2Plugin->asAnonymousClass($rowactions->config_array).";

    // combine <?php echo $rowactionsName ?>Config with arguments
    Ext.app.sx.<?php echo $rowactionsName ?>.superclass.constructor.call(this, Ext.apply(this.rowactionsConfig, c));
";
$rowactions->attributes['constructor'] = $sfExtjs2Plugin->asMethod($configArr);
?]
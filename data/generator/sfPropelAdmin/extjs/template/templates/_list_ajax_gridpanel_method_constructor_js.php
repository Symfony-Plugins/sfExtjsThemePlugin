<?php
  $moduleName = ucfirst(sfInflector::camelize($this->getModuleName()));
  $panelName = "List".$moduleName."GridPanel";
?>

[?php
// constructor
$source = '';
if(isset($gridpanel->rowExpander))
{
  $source .= "this.rowExpander = ".$sfExtjs2Plugin->asVar($gridpanel->rowExpander);
}

$source .= "
    this.cm = ".$sfExtjs2Plugin->asVar($gridpanel->column_model).";

    // combine <?php echo $panelName ?>Config with arguments
    Ext.app.sx.<?php echo $panelName ?>.superclass.constructor.call(this, Ext.apply(".$sfExtjs2Plugin->asAnonymousClass($gridpanel->config_array).", c));

    this.modulename = '<?php echo $this->getModuleName() ?>';
    this.panelType = 'list';

    // TODO: needs testing
    if ((typeof c != 'undefined') && (typeof c.filter != 'undefined')) {
      this.store.baseParams.filter = 'query';
      c.filter_key = (typeof c.filter_key != 'undefined') ? c.filter_key : -1;
      this.store.baseParams['filters['+c.filter+']'] = c.filter_key;
    }
  ";
$configArr = Array(
  'parameters' => 'c',
  'source' => $source
);

$gridpanel->attributes['constructor'] = $sfExtjs2Plugin->asMethod($configArr);
?]

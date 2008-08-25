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

    if ((typeof c != 'undefined') && (typeof c.filter != 'undefined')) {
      var baseParams = {'filter': true};
      c.filter_key = (typeof c.filter_key != 'undefined') ? c.filter_key : -1;
      baseParams['filters['+c.filter+']'] = c.filter_key;

      this.store.baseParams = baseParams;
    }
  ";
$configArr = Array(
  'parameters' => 'c',
  'source' => $source
);

$gridpanel->attributes['constructor'] = $sfExtjs2Plugin->asMethod($configArr);
?]

[?php
// constructor
$source = '';
if(isset($gridpanel->rowExpander))
{
  $source .= "this.rowExpander = ".$sfExtjs2Plugin->asVar($gridpanel->rowExpander);
}

$source .= "
    this.cm = ".$sfExtjs2Plugin->asVar($gridpanel->column_model).";

    // combine $className Config with arguments
    Ext.app.sx.$className.superclass.constructor.call(this, Ext.apply(".$sfExtjs2Plugin->asAnonymousClass($gridpanel->config_array).", c));

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
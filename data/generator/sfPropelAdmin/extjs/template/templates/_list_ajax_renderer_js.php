<?php
  $moduleName = ucfirst(sfInflector::camelize($this->getModuleName()));
  $rendererName = "List".$moduleName.'Renderers';

  $tableName = $this->getTableName();
  $prefix = $tableName.$this->tableDelimiter;

  // get PK
  $pkn = $this->getPrimaryKeyAdminColumn()->getName();

?>
[?php
$renderer = new stdClass();
$renderer->attributes = array();

  // initComponent
  $renderer->attributes['initComponent'] = $sfExtjs2Plugin->asMethod("
    //call parent
    Ext.app.sx.<?php echo $rendererName ?>.superclass.initComponent.apply(this, arguments);
  ");

  // renderLink
  $renderer->attributes['renderLink'] = $sfExtjs2Plugin->asMethod(array(
    'parameters' => 'value, params, record, rowIndex, colIndex, store',
    'source' => "
      if (record) return String.format('<u><b><a class=\'gridlink\' sf_ns:modulename=\'<?php echo $this->getPrimaryKeyAdminColumn()->getTableName() ?>\' sf_ns:key=\'{1}\' href=\'<?php echo $this->controller->genUrl($this->getModuleName().'/edit?'.$pkn.'=') ?>/{1}\'>{0}</a></b></u>',
        value,
        record.data['<?php echo $pkn ?>']
      );
    "
  ));

  // a column with a weight
  $renderer->attributes['renderWeight'] = $sfExtjs2Plugin->asMethod(array(
    'parameters' => 'v',
    'source' => "
      if (!v) return '';

      v = String(v); var ps = v.split('.');
      var whole = ps[0];
      var r = /(\d+)(\d{3})/;
      while (r.test(whole)) {
        whole = whole.replace(r, '$1' +',' + '$2');
      }
      v = whole;

      return String.format('{0} kg', v);
    "
  ));

  // formatDate
  $renderer->attributes['formatDate'] = $sfExtjs2Plugin->asMethod(array(
    'parameters' => 'v',
    'source' => "return Ext.util.Format.date(v, '<?php echo sfConfig::get('app_sf_extjs_theme_plugin_format_date', 'm/d/Y') ?>')"
  ));

  $renderer->attributes['formatLongstring'] = $sfExtjs2Plugin->asMethod(array(
    'parameters' => 'v, metaData',
    'source' => "
      metaData.css = 'x-grid3-cell-wrap';
      v = Ext.util.Format.stripTags(v);
      return Ext.util.Format.ellipsis(v, 255);
    "
  ));

  $renderer->attributes['formatNumber'] = $sfExtjs2Plugin->asMethod(array(
    'parameters' => 'v',
    'source' => "
      var vString = v.toString();
      if (vString.indexOf('.') != -1)
      {
        v.toFixed(2);
      }
      return v;
    "
  ));

  $renderer->attributes['formatBoolean'] = $sfExtjs2Plugin->asMethod(array(
    'parameters' => 'v',
    'source' => "return v ? 'Yes' : 'No';"
  ));

  $renderer->attributes['formatReadonly'] = $sfExtjs2Plugin->asMethod(array(
    'parameters' => 'v',
    'source' => "return String.format('<i>{0}</i>', v);"
  ));


<?php
$methods =  $this->getParameterValue('renderer.method');
if (isset($methods['partials'])):
if (!is_array($methods['partials']))
{
  $methods['partials'] = array($methods['partials']);
}
?>
// generator method partials
<?php
  foreach($methods['partials'] as $method):
?>
include_partial('<?php echo substr($method,1) ?>', array('sfExtjs2Plugin' => $sfExtjs2Plugin, 'renderer' => $renderer));
<?php
    $this->createPartialFile($method,'<?php // @object $sfExtjs2Plugin and @object $renderer provided ?>');
  endforeach;
endif;

$variables =  $this->getParameterValue('renderer.variable');
if (isset($variables['partials'])):
if (!is_array($variables['partials']))
{
  $variables['partials'] = array($variables['partials']);
}
?>
// generator variable partials
<?php
  foreach($variables['partials'] as $variable):
?>
include_partial('<?php echo substr($variable,1) ?>', array('sfExtjs2Plugin' => $sfExtjs2Plugin, 'renderer' => $renderer));
<?php
  $this->createPartialFile($variable,'<?php // @object $sfExtjs2Plugin and @object $renderer provided ?>');
  endforeach;
endif;
?>


  // app.sx from Symfony eXtended (instead of ux: user eXtention)
  $sfExtjs2Plugin->beginClass(
    'Ext.app.sx',
    '<?php echo $rendererName ?>',
    'Ext.grid.ColumnModel',
    $renderer->attributes
  );

  // include custom renderers //OBSOLETE soon
  //include_partial('list_ajax_renderer_js_custom', array('sfExtjs2Plugin' => $sfExtjs2Plugin));

  $sfExtjs2Plugin->endClass();
?]
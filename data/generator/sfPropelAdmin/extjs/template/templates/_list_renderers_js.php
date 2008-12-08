<?php
  $moduleName = ucfirst(sfInflector::camelize($this->getModuleName()));
  $className = "List".$moduleName.'Renderers';
  $xtype = "list".$this->getModuleName().'renderers';

  $tableName = $this->getTableName();
  $prefix = $tableName.$this->tableDelimiter;

  // get PK
  $pkn = $this->getPrimaryKeyAdminColumn()->getName();

?>
[?php
$className = '<?php echo $className ?>';
$renderers = new stdClass();
$renderers->attributes = array();

// renderLink
$renderers->attributes['renderLink'] = $sfExtjs2Plugin->asMethod(array(
  'parameters' => 'value, params, record, rowIndex, colIndex, store',
  'source' => "
    if (record) return String.format('<u><b><a class=\'gridlink\' sf_ns:modulename=\'<?php echo $this->getPrimaryKeyAdminColumn()->getTableName() ?>\' sf_ns:key=\'{1}\' href=\'<?php echo $this->controller->genUrl($this->getModuleName().'/edit?'.$pkn.'=') ?>/{1}\'>{0}</a></b></u>',
      value,
      record.data['<?php echo $pkn ?>']
    );
  "
));

// a column with a weight
$renderers->attributes['renderWeight'] = $sfExtjs2Plugin->asMethod(array(
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
$renderers->attributes['formatDate'] = $sfExtjs2Plugin->asMethod(array(
  'parameters' => 'v',
  'source' => "return Ext.util.Format.date(v, '<?php echo sfConfig::get('sf_extjs_theme_plugin_format_date', 'm/d/Y') ?>')"
));

$renderers->attributes['formatLongstring'] = $sfExtjs2Plugin->asMethod(array(
  'parameters' => 'v, metaData',
  'source' => "
    metaData.css = 'x-grid3-cell-wrap';
    v = Ext.util.Format.stripTags(v);
    return Ext.util.Format.ellipsis(v, 255);
  "
));

$renderers->attributes['formatNumber'] = $sfExtjs2Plugin->asMethod(array(
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

$renderers->attributes['formatBoolean'] = $sfExtjs2Plugin->asMethod(array(
  'parameters' => 'v',
  'source' => "return v ? 'Yes' : 'No';"
));

$renderers->attributes['formatReadonly'] = $sfExtjs2Plugin->asMethod(array(
  'parameters' => 'v',
  'source' => "return String.format('<i>{0}</i>', v);"
));
<?php echo $this->getStandardPartials('renderers', array('initComponent')) ?>
<?php echo $this->getCustomPartials('renderers','method'); ?>
<?php echo $this->getCustomPartials('renderers','variable'); ?>

// create the Ext.app.sx.<?php echo $className ?> class
$sfExtjs2Plugin->beginClass(
  'Ext.app.sx',
  '<?php echo $className ?>',
  'Ext.grid.ColumnModel',
  $renderers->attributes
);

$sfExtjs2Plugin->endClass();
?]
// register xtype
Ext.reg('<?php echo $xtype ?>', Ext.app.sx.<?php echo $className ?>);

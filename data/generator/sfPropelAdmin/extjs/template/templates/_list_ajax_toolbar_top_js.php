[?php /* * Created on 20-nov-2007 * * by Leon van der Ree */ ?]
<?php
$listActions = $this->getParameterValue('list.actions');
if (null === $listActions)
{
  $listActions = array(
    '_create' => array(),
    '_refresh' => array()
  );
}

if (is_array($listActions)): ?>
<?php
  $moduleName = ucfirst(sfInflector::camelize($this->getModuleName()));

  $toolbarName = "List".$moduleName."ToolbarTop";
?>
[?php
  //setup the configuration
  $config_items = array(
    'autoWidth' => false,
    'height' => 26,
    'items' => array()
  );

<?php foreach ((array) $listActions as $actionName => $params): ?>
  <?php // TODO: this is broken // echo $this->addCredentialCondition("\$config_items['items'][] = \$sfExtjs2Plugin->asAnonymousClass(array(".$this->getAjaxButtonToToolbarAction($actionName, $params, false)."));\n\n", $params) ?>
  $config_items['items'][] = array(<?php echo $this->getAjaxButtonToToolbarAction($actionName, $params, false) ?>);
<?php endforeach ?>

  // constructor
  $sfExtjs2_<?php echo $toolbarName ?>_constructor = "
    // combine <?php echo $toolbarName ?>Config with arguments
    Ext.app.sx.<?php echo $toolbarName ?>.superclass.constructor.call(this, Ext.apply(".$sfExtjs2Plugin->asAnonymousClass($config_items).", c));
  ";

  // initComponent
  $sfExtjs2_<?php echo $toolbarName ?>_initComponent = "
    //call parent
    Ext.app.sx.<?php echo $toolbarName ?>.superclass.initComponent.apply(this, arguments);
  ";


  // app.sx from Symfony eXtended (instead of ux: user eXtention)
  $sfExtjs2Plugin->beginClass(
    'Ext.app.sx',
    '<?php echo $toolbarName ?>',
    'Ext.Toolbar',
    array (
      'constructor'   => $sfExtjs2Plugin->asMethod(array(
        'parameters' => 'c',
        'source'     => $sfExtjs2_<?php echo $toolbarName ?>_constructor
      )),
      'initComponent' => $sfExtjs2Plugin->asMethod($sfExtjs2_<?php echo $toolbarName ?>_initComponent),
    )
  );
  $sfExtjs2Plugin->endClass();

?]
<?php endif; ?>
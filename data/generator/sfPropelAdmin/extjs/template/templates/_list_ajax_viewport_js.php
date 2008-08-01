[?php
?]
<?php $moduleName = sfInflector::camelize($this->getModuleName()) ?>
  var viewport = [?php echo $sfExtjs2Plugin->Viewport(array(
    'layout'  => 'fit',
    'items'   => array(
      $sfExtjs2Plugin->TabPanel(array(
        'region'    => 'center',
        'items'     => array(
          $sfExtjs2Plugin->asVar('list<?php echo $moduleName ?>GridPanel'),
        ),
      ))
    ),
  )); ?]

  viewport.doLayout();
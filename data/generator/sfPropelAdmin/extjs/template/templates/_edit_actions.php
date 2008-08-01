<ul class="sf_admin_actions">
<?php $editActions = $this->getParameterValue('edit.actions') ?>
<?php if (null !== $editActions): ?>
<?php foreach ((array) $editActions as $actionName => $params): ?>
  <?php if ($actionName == '_delete') continue ?>
  <?php echo $this->addCredentialCondition($this->getButtonToAction($actionName, $params, true), $params) ?>
<?php endforeach; ?>
<?php else: ?>
  <?php echo $this->getButtonToAction('_list', array('name' => 'Cancel'), true) ?>
  <?php echo $this->getButtonToAction('_save_and_list', array('name' => 'Save'), true) ?>
  <?php echo $this->getButtonToAction('_save_and_add', array('name' => 'Save and add new '.$this->getSingularName()), true) ?>
<?php endif; ?>
</ul>

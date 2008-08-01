[?php use_helper('Object', 'Validation', 'ObjectAdmin', 'I18N', 'Date') ?]

<?php if (false): ?>
[?php use_stylesheet('<?php echo $this->getParameterValue('css', sfConfig::get('sf_admin_web_dir').'/css/main') ?>') ?]
<?php endif; ?>


<div id="sf_admin_container">

<?php $objectName = $this->getParameterValue('object_name', $this->getModuleName()) ?>
[?php if ($<?php echo $this->getSingularName() ?>->isNew()): ?]
<h1><?php echo $this->getI18NString('edit.newtitle', 'Add '.$objectName); ?></h1>
[?php else: ?]
<h1><?php echo $this->getI18NString('edit.title', 'Edit '.$objectName); ?></h1>
[?php endif; ?]

<div id="sf_admin_header">
[?php include_partial(edit_header', array('<?php echo $this->getSingularName() ?>' => $<?php echo $this->getSingularName() ?>)) ?]
</div>

<div id="sf_admin_content">
[?php include_partial(edit_messages', array('<?php echo $this->getSingularName() ?>' => $<?php echo $this->getSingularName() ?>, 'labels' => $labels)) ?]
[?php include_partial(edit_form', array('<?php echo $this->getSingularName() ?>' => $<?php echo $this->getSingularName() ?>, 'labels' => $labels)) ?]
</div>

<div id="sf_admin_footer">
[?php include_partial(edit_footer', array('<?php echo $this->getSingularName() ?>' => $<?php echo $this->getSingularName() ?>)) ?]
</div>

</div>

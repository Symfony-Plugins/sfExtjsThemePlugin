<td colspan="<?php //echo count($this->getColumns('list.display'))  //disabled for now since field +* brakes things, we should use the new generator anyway ?>">
<?php if ($this->getParameterValue('list.params')): ?>
  <?php echo $this->getI18NString('list.params') ?>
<?php else: ?>
<?php $hides = $this->getParameterValue('list.hide', array()) ?>
<?php //foreach ($this->getColumns('list.display') as $column):  also disabled for now, since +* brakes things ?>
<?php if (false) : //in_array($column->getName(), $hides)) continue ?>
  <?php if ($column->isLink()): ?>
  [?php echo link_to(<?php echo $this->getColumnListTag($column) ?> ? <?php echo $this->getColumnListTag($column) ?> : __('-'), '<?php echo $this->getModuleName() ?>/edit?<?php echo $this->getPrimaryKeyUrlParams() ?>) ?]
  <?php else: ?>
  [?php echo <?php echo $this->getColumnListTag($column) ?> ?]
  <?php endif; ?>
   -
<?php // endforeach; ?>
<?php endif; ?>
<?php endif; ?>
</td>
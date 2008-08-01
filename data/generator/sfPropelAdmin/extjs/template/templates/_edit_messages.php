<?php if ($this->getParameterValue('ajax', sfConfig::get('app_sf_extjs_theme_plugin_ajax', true))): ?>
<script type="text/javascript">

[?php if ($sf_request->hasErrors()): ?]
//  Message.msg('test','[?php echo __('There are some errors that prevent the form to validate')?]', 10, 'x-box-red');


<div class="form-errors">
<h2>[?php echo __('There are some errors that prevent the form to validate') ?]</h2>
<dl>
[?php foreach ($sf_request->getErrorNames() as $name): ?]
  <dt>[?php echo __($labels[$name]) ?]</dt>
  <dd>[?php echo $sf_request->getError($name) ?]</dd>
[?php endforeach; ?]
</dl>
</div>
[?php elseif ($sf_flash->has('notice')): ?]
  //Message.msg('test','[?php echo __($sf_flash->get('notice')) ?]');
[?php endif; ?]

</script>

<?php else: ?>

[?php if ($sf_request->hasErrors()): ?]
<div class="form-errors">
<h2>[?php echo __('There are some errors that prevent the form to validate') ?]</h2>
<dl>
[?php foreach ($sf_request->getErrorNames() as $name): ?]
  <dt>[?php echo __($labels[$name]) ?]</dt>
  <dd>[?php echo $sf_request->getError($name) ?]</dd>
[?php endforeach; ?]
</dl>
</div>
[?php elseif ($sf_flash->has('notice')): ?]
<div class="save-ok">
<h2>[?php echo __($sf_flash->get('notice')) ?]</h2>
</div>
[?php endif; ?]

<?php endif; ?>
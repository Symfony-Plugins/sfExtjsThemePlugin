[?php
<?php $css = sfConfig::get('extjs2_dbfgen_default_stylesheets', array()); ?>
<?php $js = sfConfig::get('extjs2_dbfgen_default_javascripts', array()); ?>
<?php if ($use_tinymce = sfConfig::get('app_extjs2_dbfgen_theme_plugin_use_tinymce', false)): ?>
   $js[] = '/sfExtjsThemePlugin/js/tiny_mce/tiny_mce';
   $js[] = '/sfExtjsThemePlugin/js/ext.ux.tinymce/Ext.ux.TinyMCE.min.js';
   $js[] = '/sfExtjsThemePlugin/js/ext.ux.managediframe/miframe-min.js';
<?php endif ?>

// TODO: Need to put in a mechanism to only include extensions we are currently using in the generator.yml
$sfExtjs2Plugin = new sfExtjs2Plugin(
  array(
    'theme'   => sfConfig::get('app_extjs2_dbfgen_theme_plugin_theme'),
    'adapter' => '<?php echo $this->getParameterValue('adapter'); ?>'
  ),
  array(
    'css' => <?php var_export($css) ?>,
    'js'  => <?php var_export($js) ?>
  )
);
$sfExtjs2Plugin->load();
?]
<?php
  $moduleName = sfInflector::camelize($this->getModuleName());

  $groupedColumns = $this->getColumnsGrouped('list.display', true);
  $pk = $groupedColumns['pk'];
?>

[?php use_helper('I18N', 'Date') ?]
[?php use_helper('Javascript') ?]
[?php use_helper('PJS') ?]

[?php use_javascript(sfPJSHelper::pjs_path('<?php echo $this->getModuleName() ?>/listAjaxGridPanelJs', false, array()), 'last'); //TODO: change sfPjsPlugin to accept first/last/none ?]
[?php use_javascript(sfPJSHelper::pjs_path('<?php echo $this->getModuleName() ?>/listAjaxJs', false, array()), 'last'); ?]

  <script type="text/javascript">
    // initialise CodeLoader
    Ext.app.CodeLoader = new Ext.ux.ModuleManager({modulePath: '[?php echo $this->getContext()->getRequest()->getScriptName() ?]' });

<?php if ($use_tinymce = sfConfig::get('app_sf_extjs_theme_plugin_use_tinymce', false)): ?>
    //init TinyMCE
    Ext.ux.TinyMCE.initTinyMCE();
<?php endif ?>
  </script>

[?php
$css = sfConfig::get('extjs_default_stylesheets', array());
$js = sfConfig::get('extjs_default_javascripts', array());
<?php if ($use_tinymce): ?>
   $js[] = '/sfExtjsThemePlugin/js/tiny_mce/tiny_mce';
   $js[] = '/sfExtjsThemePlugin/js/ext.ux.tinymce/Ext.ux.TinyMCE.min.js';
   $js[] = '/sfExtjsThemePlugin/js/ext.ux.managediframe/miframe-min.js';
<?php endif ?>

// TODO: Need to put in a mechanism to only include extensions we are currently using in the generator.yml
$sfExtjs2Plugin = new sfExtjs2Plugin(
  array(
    'theme'   => sfConfig::get('app_sf_extjs_theme_plugin_theme'),
    'adapter' => '<?php echo $this->getParameterValue('adapter'); ?>'
  ),
  array(
    'css' => $css,
    'js'  => $js
  )
);
$sfExtjs2Plugin->load();
?]

<?php if(false): //OBSOLETE


// - OBSOLETE - OBSOLETE -  OBSOLETE -  OBSOLETE -  OBSOLETE -  OBSOLETE -  OBSOLETE -  OBSOLETE -  OBSOLETE -
/*************************************************************************************************************
 *  or at least out-dated (if you don't want to use a viewport, you need to alter things below...) ?>        *
 *************************************************************************************************************/
// - OBSOLETE - OBSOLETE -  OBSOLETE -  OBSOLETE -  OBSOLETE -  OBSOLETE -  OBSOLETE -  OBSOLETE -  OBSOLETE -
?>

<div id="sf_admin_container">

  <div id="sf_admin_header">
    [?php include_partial('list_header', array('pager' => $pager)) ?]
    [?php include_partial('list_messages', array('pager' => $pager)) ?]
  </div>

  <div id="sf_admin_bar">
<?php if ($this->getParameterValue('list.filters')): ?>
  [?php //include_partial('filters', array('filters' => $filters)) // filters loaded at different place? ???? >LvanderRee please ask again at forum, don't know who said this, but filters aren't implemented yet, it needs to be reimplemted in its own panel, so it can be reused at any location you want ?]
<?php endif; ?>
  </div>

  <div id="htmlMessageBox"></div>
  <?php if ($this->getParameterValue('border_panel', sfConfig::get('app_sf_extjs_theme_plugin_border_panel', true))): ?>
  <div style="width:100%;" class="x-gray">
      <div class="x-box-tl"><div class="x-box-tr"><div class="x-box-tc"></div></div></div>
      <div class="x-box-ml"><div class="x-box-mr"><div class="x-box-mc">

          <?php $objectName = $this->getParameterValue('object_name', $this->getModuleName()) ?>
          <h3 style="margin-bottom:5px;"><?php echo $this->getI18NString('list.title', $objectName.' overview') ?></h3>
          <div id="ajax-border" style="border:1px solid transparent;">
  <?php endif; ?>

            <div id="tabs"></div>
            <div id="tabContent">
              <div id="htmlPanelToolbar"></div>
              <div id="htmlPanelGrid"></div>
            </div>

  <?php if ($this->getParameterValue('border_panel', sfConfig::get('app_sf_extjs_theme_plugin_border_panel', true))): ?>
          </div>
      </div></div></div>
      <div class="x-box-bl"><div class="x-box-br"><div class="x-box-bc"></div></div></div>
  </div>
  <?php endif; ?>

  <div id="sf_admin_footer">
  [?php include_partial('list_footer', array('pager' => $pager)) ?]
  </div>

</div>

<?php endif; ?>

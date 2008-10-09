[?php use_helper('PJS') ?]
[?php use_javascript(sfPJSHelper::pjs_path('<?php echo $this->getModuleName() ?>/listAjaxGridPanelJs', false, array()), 'last'); //TODO: change sfPjsPlugin to accept first/last/none ?]
[?php use_javascript(sfPJSHelper::pjs_path('<?php echo $this->getModuleName() ?>/listAjaxJs', false, array()), 'last'); ?]

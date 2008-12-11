<?php
$edit_key = 'edit';
$edit_ns = ucfirst(sfInflector::camelize($this->getModuleName()))."Edit";
$moduleName = sfInflector::camelize($this->getModuleName());
$className = "Edit".$moduleName."FormPanel";
$xtype = "edit".$this->getModuleName()."formpanel";
$groupedColumns = $this->getColumnsGrouped($edit_key.'.display', true);
?>
[?php
$className = '<?php echo $className ?>';
$formpanel = new stdClass();
$formpanel->attributes = array();
$formpanel->methods    = array();

//TODO: rewrite with include partial, without ob_start
ob_start();
  require('_edit_reader.php');
$formpanel->reader = trim(ob_get_clean());

<?php $objectName = $this->getParameterValue('object_name', $this->getModuleName()) ?>

$formpanel->config_array = array(
  'xtype'               => 'form',
  'title'               => '<?php echo 'New '.$objectName ?>',
<?php if (($width = $this->getParameterValue('edit.params.width', 400)) != 'fill'): ?>
  'width'               => <?php echo $width  ?>,
<?php endif; ?>
  'autoScroll'          => true,
  'labelWidth'          => 120,
  'labelAlign'          => 'left',
  'defaultType'         => 'textfield',
  'bodyStyle'           => 'padding: 10px 0px 10px 5px;',
  'reader'              => $formpanel->reader,
  'trackResetOnLoad'    => true,
  'baseParams'          => array(
    'cmd' => 'load',
  ),
  'url'                 => '<?php echo $this->controller->genUrl($this->getModuleName().'/edit') ?>',
  'method'              => 'post'
);
<?php
  $user_params = $this->getParameterValue('edit.params', null);
  if (is_array($user_params)):
?>
$formpanel->config_array = array_merge($formpanel->config_array, <?php var_export($user_params) ?>);
<?php endif; ?>

<?php
  //TODO: move this to the generator
  // add fields, fieldsets, tab-pages and buttons
  include('__edit_form_inner.php');
?>

<?php echo $this->getStandardPartials('formpanel', array('constructor','initComponent','initEvents'), 'edit') ?>
<?php echo $this->getCustomPartials('formpanel','method'); ?>
<?php echo $this->getCustomPartials('formpanel','variable'); ?>

<?php echo $this->getClassGetters('gridpanel',array('key')); ?>

$formpanel->methods['setKey'] = $sfExtjs2Plugin->asMethod(array(
  'parameters'  => 'key',
  'source'      => "
  var old_key = this.key;
  if (old_key != key)
  {
    this.key = key;
    //fire keychange event
    this.fireEvent('keychange', this.key, old_key, this);
  }
"
));

$formpanel->methods['isNew'] = $sfExtjs2Plugin->asMethod("
  return ((typeof this.key=='undefined') || (this.key==null));
");

// updateButtonsVisibility
$formpanel->methods['updateButtonsVisibility'] = $sfExtjs2Plugin->asMethod("
  // hide delete button when new item
  if (this.topToolbar)
  {
    var len;
    var topToolbar = (typeof this.topToolbar.items != 'undefined')?this.topToolbar.items.items:this.topToolbar;
    for(var i = 0, len = topToolbar.length; i < len; i++)
    {
      var button = topToolbar[i];
      if((typeof button.hide_when_new!='undefined') && button.hide_when_new)
      {
        if(typeof button.rendered == 'undefined')
        {
          button.hidden = this.isNew();
        }
        else
        {
          button.setVisible(this.isNew()?false:true);
        }
      }
    }
  }
");

// loadItem
$formpanel->methods['loadItem'] = $sfExtjs2Plugin->asMethod("
  if (this.isNew())
  {
    //cancel loading if no key set or new item
    return;
  }

  var load_config = ".$sfExtjs2Plugin->asAnonymousClass(array(
    'waitMsg' => 'Loading data',
    'success' => $sfExtjs2Plugin->asVar('this.onLoadSuccess'),
    'failure' => $sfExtjs2Plugin->asVar('this.onLoadFailure'),
    'scope'   => $sfExtjs2Plugin->asVar('this'),
    'params'  => array(
      'key' => $sfExtjs2Plugin->asVar('this.key'),
    )
  )).";

  this.getForm().load(load_config);
");


$formpanel->methods['onLoadFailure'] = $sfExtjs2Plugin->asMethod(array(
  'parameters'  => 'form, action',
  'source'      => "
  Ext.Msg.show({
    title:'Loading failed!',
    msg: 'Loading of the data failed!',
    buttons: Ext.Msg.OK
  });
  //throw load (item) failed
  this.fireEvent('load_item_failed', this);
"
));


$formpanel->methods['onLoadSuccess'] = $sfExtjs2Plugin->asMethod(array(
  'parameters'  => 'form, action',
  'source'      => "
  this.setTitle(action.reader.jsonData.title);
  //throw load (item) succes
  this.fireEvent('load_item_success', this);
"
));


$formpanel->methods['doSubmit'] = $sfExtjs2Plugin->asMethod(array(
  'parameters'  => 'submitnew',
  'source'      => "
  if (!this.getForm().isValid())
  {
    Ext.Msg.show(".$sfExtjs2Plugin->asAnonymousClass(array(
      'title'   =>  'Problem',
      'msg'     =>  'Not all fields (in every tab-page) contain valid data,<br>Please check all fields first',
      'modal'   =>  true,
      'icon'    =>  $sfExtjs2Plugin->asVar('Ext.Msg.INFO'),
      'buttons' =>  $sfExtjs2Plugin->asVar('Ext.Msg.OK')
    )).");
  }
  else
  {
    // add key to url if key is set to an existing primary-key and submitnew isn't set
    var url_key = (!this.isNew()&& typeof submitnew == 'undefined')?this.key:'';

<?php if ($this->getParameterValue('tinyMCE', false)): ?>
    tinyMCE.triggerSave();
<?php endif; ?>
    this.getForm().submit(".$sfExtjs2Plugin->asAnonymousClass(array(
      'url'     => $sfExtjs2Plugin->asVar('this.url'),
      'scope'   => $sfExtjs2Plugin->asVar('this'),
      'success' => $sfExtjs2Plugin->asVar('this.onSubmitSuccess'),
      'failure' => $sfExtjs2Plugin->asVar('this.onSubmitFailure'),
      'params'  => $sfExtjs2Plugin->asAnonymousClass(array('cmd' => 'save', '<?php echo sfInflector::underscore($groupedColumns['pk']->getPhpName()) ?>'=> $sfExtjs2Plugin->asVar('url_key'))),
      'waitMsg' => 'Saving...'
    )).");
  }
"
));

$formpanel->methods['onSubmitSuccess'] = $sfExtjs2Plugin->asMethod(array(
  'parameters'  => 'form, action',
  'source'      => "
  this.setKey(action.result.id);
  this.setTitle(action.result.title);

  // update all fields their originalValue
  if (this.trackResetOnLoad)
  {
    form.items.each(function (i)
    {
      if (i.isFormField)
      {
        if (i.xtype != 'tinymce')
        {
          i.originalValue = i.getValue();
        }
        else
        {
          i.ed.startContent = i.ed.getContent({format : 'raw', no_events : 1});
        }
      }
    });
  }

  // show/hide appropriate buttons
  this.updateButtonsVisibility();

  //fire saved event
  this.fireEvent('saved', this);

//  //TODO: maybe re-enable this by adding a config-option, which can also set the succes message
//  Ext.Msg.show({
//    title:'Success',
//    msg:'Form submitted successfully!',
//    modal:true,
//    icon:Ext.Msg.INFO,
//    buttons:Ext.Msg.OK
//  });
"
));

$formpanel->methods['onSubmitFailure'] = $sfExtjs2Plugin->asMethod(array(
  'parameters'  => 'form, action',
  'source'      => "
  //fire saved event
  this.fireEvent('save_failed', this);

  var msg = 'Unknown problem';
  if (action.result)
  {
    msg = action.result.error || action.response.responseText;
  }
  this.showError(msg);
"
));

// deleteItem
$formpanel->methods['deleteItem'] = $sfExtjs2Plugin->asMethod("
  Ext.Msg.confirm('Confirm','Are you surse you want to delete this?',function(btn,text)
  {
    if(btn == 'yes')
    {
      var onSuccess = function(response)
      {
        try
        {
          var json_response = Ext.util.JSON.decode(response.responseText);
          //check for application-level failure and redirect if necessary
          if (json_response.success === false) return this.failure(response);
        }
        catch (e) {};
        this.item.ownerCt.getComponent(0).store.reload();
        this.item.ownerCt.remove(this.item);

        //fire deleted event
        this.item.fireEvent('deleted', this.item);
      };

      var onFailure = function(response)
      {
        var message = 'Error while deleting!';
        try
        {
          var json_response = Ext.util.JSON.decode(response.responseText);
          message  = json_response.message;
        } catch (e) {};
        Ext.Msg.alert('Error', message);
      };

      Ext.Ajax.request({
        url: '<?php echo $this->controller->genUrl($this->getModuleName().'/delete') ?>',
        method: 'POST',
        params: {id: this.key},
        scope: {item: this, success: onSuccess, failure: onFailure},
        success: onSuccess,
        failure: onFailure
      });
    }
  }, this);
");

$formpanel->methods['showError'] = $sfExtjs2Plugin->asMethod(array(
  'parameters'  => 'msg, title',
  'source'      => "
  var title = title || 'Error';
  Ext.Msg.show(".$sfExtjs2Plugin->asAnonymousClass(array(
    'title'   => $sfExtjs2Plugin->asVar('title'),
    'msg'     => $sfExtjs2Plugin->asVar('msg'),
    'modal'   => true,
    'icon'    => $sfExtjs2Plugin->asVar('Ext.Msg.ERROR'),
    'buttons' => $sfExtjs2Plugin->asVar('Ext.Msg.OK')
  )).");
"
));

// app.sx from Symfony eXtended (instead of ux: user eXtention)
$sfExtjs2Plugin->beginClass(
  'Ext.app.sx',
  '<?php echo $className ?>',
  'Ext.FormPanel',
  array_merge(
    $formpanel->methods,
    $formpanel->attributes
  )
);
$sfExtjs2Plugin->endClass();

?]
// register xtype
Ext.reg('<?php echo $xtype ?>', Ext.app.sx.<?php echo $className ?>);

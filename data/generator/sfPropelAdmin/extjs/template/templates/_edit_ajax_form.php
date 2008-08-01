<?php
$edit_key = 'edit';

$edit_ns = ucfirst(sfInflector::camelize($this->getModuleName()))."Edit";
$moduleName = sfInflector::camelize($this->getModuleName());
$formName = "Edit".$moduleName."FormPanel";
$formName_xtype = strtolower("Edit".$this->getModuleName()."FormPanel");

$groupedColumns = $this->getColumnsGrouped($edit_key.'.display', true);
$pkn = $groupedColumns['pk']->getName();

?>
[?php
use_helper('I18N', 'Date');
sfLoader::loadHelpers('Extjs');

$formpanel = new stdClass();
$formpanel->attributes = array();
$formpanel->methods    = array();

//TODO: rewrite with include partial, without ob_start
ob_start();
  require('_edit_ajax_reader.php');
$formpanel->reader = trim(ob_get_clean());


<?php $objectName = $this->getParameterValue('object_name', $this->getModuleName()) ?>

$formpanel->config_array = array(
  'xtype'               => 'form',
//  'baseCls              => 'x-plain',

  'title'               => <?php echo $this->getI18NString('edit.newtitle', 'New '.$objectName, false) ?>,

<?php if (($width = $this->getParameterValue('edit.params.width', 400)) != 'fill'): ?>
  'width'               => <?php echo $width  ?>,
<?php endif; ?>
//  'autoHeight'          => true,
//  'height'              => 200,

  'autoScroll'          => true,

  'labelWidth'          => 120,
  'labelAlign'          => 'left',
  'frame'               => true,

//  'bodyStyle'           => 'padding: 5px 5px 10px 10px;',

//  'layoutConfig'        => array(),
  'defaults'            => array(
                          'margin' => '0px -5px',
//                        'width' => 230
                        ),
  'defaultType'         => 'textfield',

  'reader'              => $formpanel->reader,
  'trackResetOnLoad'    => true,

  'baseParams'          => array(
    'cmd' => 'load',
  ),
  'url'                 => '<?php echo $this->controller->genUrl($this->getModuleName().'/ajaxEdit') ?>',
  'method'              => 'post',

);
<?php
  $user_params = $this->getParameterValue('edit.params', null);
  if (is_array($user_params)):
?>
$formpanel->config_array = array_merge($formpanel->config_array, <?php var_export($user_params) ?>);
<?php endif; ?>
<?php


$methods =  $this->getParameterValue('formpanel.method');
if (isset($methods['partials'])):
if (!is_array($methods['partials']))
{
  $methods['partials'] = array($methods['partials']);
}
?>
// generator method partials
<?php
  foreach($methods['partials'] as $method):
?>
include_partial('<?php echo substr($method,1) ?>', array('sfExtjs2Plugin' => $sfExtjs2Plugin, 'formpanel' => $formpanel));
<?php
    $this->createPartialFile($method,'<?php // @object $sfExtjs2Plugin and @object $formpanel provided ?>');
  endforeach;
endif;


?>
<?php
  // add fields, fieldsets, tab-pages and buttons
  include('__edit_ajax_form_inner.php');
?>


// constructor
$formpanel->methods['constructor'] = $sfExtjs2Plugin->asMethod(array(
      'parameters' => 'c',
      'source'     => "
        // combine <?php echo $formName ?>Config with arguments
        Ext.app.sx.<?php echo $formName ?>.superclass.constructor.call(this, Ext.apply(".$sfExtjs2Plugin->asAnonymousClass($formpanel->config_array).", c));

        this.modulename = '<?php echo $this->getModuleName() ?>';
        this.panelType = 'edit';
      "));

// initComponent
$formpanel->methods['initComponent'] = $sfExtjs2Plugin->asMethod("
  //call parent
  Ext.app.sx.<?php echo $formName ?>.superclass.initComponent.apply(this, arguments);

  this.addEvents(
    /**
     * @event load_item_success
     * Fires when the item is loaded successfully
     * @param {Ext.app.sx.<?php echo $formName ?>} this Edit-FormPanel
     */
    'load_item_success',
    /**
     * @event saved
     * Fires when the item is saved successfully
     * @param {Ext.app.sx.<?php echo $formName ?>} this Edit-FormPanel
     */
    'saved',
    /**
     * @event save_failed
     * Fires when the item is not saved successfully
     * @param {Ext.app.sx.<?php echo $formName ?>} this Edit-FormPanel
     */
    'save_failed',
    /**
     * @event deleted
     * Fires when the item is deleted successfully
     * @param {Ext.app.sx.<?php echo $formName ?>} this Edit-FormPanel
     */
    'deleted',
    /**
     * @event close_request
     * Fires when the panel request to close itself (it cannot do this itself, the window/tabpabel should do this)
     * @param {Ext.app.sx.<?php echo $formName ?>} this Edit-FormPanel
     */
    'close_request',
    /**
     * @event keychange
     * Fires when the items (primary) key has been set (after saving a new item)
     * @param number key
     * @param number oldkey
     * @param {Ext.app.sx.<?php echo $formName ?>} this Edit-FormPanel
     */
     'keychange'
  );

  // show/hide appropriate buttons
  this.updateButtonsVisibility();

");

// obsolete, use: iconCls: "Ext.ux.IconMgr.getIcon('icon-file')"
//$formpanel->methods['afterRender'] = $sfExtjs2Plugin->asMethod(array(
//  'parameters' => 'ct',
//  'source'     => "
//    //call parent
//    Ext.app.sx.<?php echo $formName ?>.superclass.afterRender.apply(this, arguments);
//
//    Ext.app.IconMgrCreate.defer(25, this, [ct.dom]); //TODO: how to get rid of the defer method?
//"));


$formpanel->methods['getModulename'] = $sfExtjs2Plugin->asMethod("
  return this.modulename;
");

$formpanel->methods['getPanelType'] = $sfExtjs2Plugin->asMethod("
  return this.panelType;
");

$formpanel->methods['getKey'] = $sfExtjs2Plugin->asMethod("
  return this.key;
");

$formpanel->methods['setKey'] = $sfExtjs2Plugin->asMethod(array(
      'parameters'  => 'key',
      'source'      => "
        var old_key = this.key;
        if (old_key != key) {
          this.key = key;

          //fire keychange event
          this.fireEvent('keychange', this.key, old_key, this);
        }
      "
));

$formpanel->methods['isNew'] = $sfExtjs2Plugin->asMethod("
  return ((typeof this.key=='undefined') || (this.key==null) || (this.key=='create_<?php echo $this->getModuleName() ?>'));
");

// updateButtonsVisibility
$formpanel->methods['updateButtonsVisibility'] = $sfExtjs2Plugin->asMethod("
  // hide delete button when new item
  if (this.buttons) {
    for(var i = 0, len = this.buttons.length; i < len; i++){
      var button = this.buttons[i];
      if((typeof button.hide_when_new!='undefined') && button.hide_when_new){
        button.setVisible(!this.isNew());
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
    'scope'   => $sfExtjs2Plugin->asVar('this'),
    'params'  => array(
        'key' => $sfExtjs2Plugin->asVar('this.key'),
    )
  )).";

  this.getForm().load(load_config);
");

$formpanel->methods['onLoadSuccess'] = $sfExtjs2Plugin->asMethod(array(
      'parameters'  => 'form, action',
      'source'      => "
        this.setTitle(action.reader.jsonData.title);
        //throw load (item) succes
        this.fireEvent('load_item_success', this);
      "
));


$formpanel->methods['doSubmit'] = $sfExtjs2Plugin->asMethod("
  if (!this.getForm().isValid()) {
    Ext.Msg.show(".$sfExtjs2Plugin->asAnonymousClass(array(
      'title'   =>  'Problem',
      'msg'     =>  'Not all fields (in every tab-page) contain valid data,<br>Please check all fields first',
      'modal'   =>  true,
      'icon'    =>  $sfExtjs2Plugin->asVar('Ext.Msg.INFO'),
      'buttons' =>  $sfExtjs2Plugin->asVar('Ext.Msg.OK')
    )).");
  } else {
    var url_key = '';
    // add key to url if key is set to an existing primary-key
    if (!this.isNew())
    {
      url_key = '?<?php echo sfInflector::underscore($groupedColumns['pk']->getPhpName()) ?>='+this.key;
    }
    this.getForm().submit(".$sfExtjs2Plugin->asAnonymousClass(array(
      'url'     => $sfExtjs2Plugin->asVar('this.url + url_key'),
      'scope'   => $sfExtjs2Plugin->asVar('this'),
      'success' => $sfExtjs2Plugin->asVar('this.onSubmitSuccess'),
      'failure' => $sfExtjs2Plugin->asVar('this.onSubmitFailure'),
      'params'  => $sfExtjs2Plugin->asAnonymousClass(array('cmd' => 'save')),
      'waitMsg' => 'Saving...'
    )).");
  }
");

$formpanel->methods['onSubmitSuccess'] = $sfExtjs2Plugin->asMethod(array(
      'parameters'  => 'form, action',
      'source'      => "
        this.setKey(action.result.id);
        this.setTitle(action.result.title);

        // update all fields their originalValue
        if (this.trackResetOnLoad) {
          form.items.each(function (i) {
            if (i.isFormField) {
              i.originalValue = i.getValue();
            }
          });
        }

        // show/hide appropriate buttons
        this.updateButtonsVisibility();

        //fire saved event
        this.fireEvent('saved', this);

        //TODO: maybe re-enable this by adding a config-option, which can also set the succes message
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
  Ext.Msg.confirm('Confirm','Are you surse you want to delete this?',function(btn,text){
    if(btn == 'yes'){
      var onSuccess = function(response){
        var message = 'Item successfully deleted';
        try {
          var json_response = Ext.util.JSON.decode(response.responseText);

          //check for application-level failure and redirect if necessary
          if (json_response.success === false) return this.failure(response);

          message = json_response.message;
        } catch (e) {};
//          Ext.Msg.alert('Delete Status',message);

        //fire deleted event
        this.item.fireEvent('deleted', this.item);
      };

      var onFailure = function(response){
        var message = 'Error while deleting!';
        try {
          var json_response = Ext.util.JSON.decode(response.responseText);
          message  = json_response.message;
        } catch (e) {};
        Ext.Msg.alert('Error', message);
      };

      Ext.Ajax.request({
        url: '<?php echo $this->controller->genUrl($this->getModuleName().'/ajaxDelete') ?>',
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
        title = title || 'Error';
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
  '<?php echo $formName ?>',
  'Ext.FormPanel',
  array_merge(
    $formpanel->methods,
    $formpanel->attributes
  )
);
$sfExtjs2Plugin->endClass();

?]
// register xtype
Ext.reg('<?php echo $formName_xtype ?>', Ext.app.sx.<?php echo $formName ?>);

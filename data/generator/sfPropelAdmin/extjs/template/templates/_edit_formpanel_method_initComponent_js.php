[?php
// constructor
$configArr = array(
  'source' => "
Ext.app.sx.$className.superclass.initComponent.apply(this, arguments);

this.addEvents(
  /**
   * @event load_item_failed
   * Fires when the item is not loaded successfully
   * @param {Ext.app.sx.$className} this Edit-FormPanel
   */
  'load_item_failed',
  /**
   * @event load_item_success
   * Fires when the item is loaded successfully
   * @param {Ext.app.sx.$className} this Edit-FormPanel
   */
  'load_item_success',
  /**
   * @event saved
   * Fires when the item is saved successfully
   * @param {Ext.app.sx.$className} this Edit-FormPanel
   */
  'saved',
  /**
   * @event save_failed
   * Fires when the item is not saved successfully
   * @param {Ext.app.sx.$className} this Edit-FormPanel
   */
  'save_failed',
  /**
   * @event deleted
   * Fires when the item is deleted successfully
   * @param {Ext.app.sx.$className} this Edit-FormPanel
   */
  'deleted',
  /**
   * @event close_request
   * Fires when the panel request to close itself (it cannot do this itself, the window/tabpabel should do this)
   * @param {Ext.app.sx.$className} this Edit-FormPanel
   */
  'close_request',
  /**
   * @event keychange
   * Fires when the items (primary) key has been set (after saving a new item)
   * @param number key
   * @param number oldkey
   * @param {Ext.app.sx.$className} this Edit-FormPanel
   */
   'keychange'
);
// show/hide appropriate buttons
this.loadItem();
this.updateButtonsVisibility();
"
);

$formpanel->attributes['initComponent'] = $sfExtjs2Plugin->asMethod($configArr);
?]
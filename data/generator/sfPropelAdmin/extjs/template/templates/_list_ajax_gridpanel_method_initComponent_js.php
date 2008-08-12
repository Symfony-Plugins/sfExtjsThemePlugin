<?php
  $moduleName = ucfirst(sfInflector::camelize($this->getModuleName()));

  $gridPanelName = "List".$moduleName."GridPanel";

  $listActions = $this->getParameterValue('list.actions');
  $bbar = $this->getParameterValue('list.params.bbar');
  /*
   * list.actions defines the actions in the toptoolbar of the grid
   *
   * list.actions takes an array of partials that define items that can be added to a toolbar
   *
   * if list.actions is not defined in generator.yml two default actions are added
   * if list.actions is set to an empty array ( [] ) in generator.yml then there will be an empty bar
   * if list.actions is set to false then no toptoolbar will be namespace will be generated
   *
   */
?>
[?php
// constructor
$configArr = array(
  'source' => "
    // initialise items which use this grid's-store
<?php if(is_array($listActions) || null === $listActions): ?>
    this.tbar = new Ext.app.sx.<?php echo "List".$moduleName."ToolbarTop" ?>({store: this.ds});
<?php endif; ?>
<?php if (!isset($bbar)): ?>
    this.bbar = new Ext.app.sx.<?php echo "List".$moduleName."ToolbarPaging" ?>({store: this.ds});
<?php elseif ($bbar != false): ?>
  this.bbar = <?php echo $bbar ?>;
<?php endif; ?>

    Ext.app.sx.<?php echo $gridPanelName ?>.superclass.initComponent.apply(this, arguments);

    //TODO these events should be implemented
    this.addEvents(
      /**
       * @event saved
       * Fires when an item is saved successfully
       * @param {Ext.app.sx.<?php echo $gridPanelName ?>} this List-GridPanel
       */
      'saved',
      /**
       * @event save_failed
       * Fires when an item is not saved successfully
       * @param {Ext.app.sx.<?php echo $gridPanelName ?>} this List-GridPanel
       */
      'save_failed',
      /**
       * @event deleted
       * Fires when an item is deleted successfully
       * @param {Ext.app.sx.<?php echo $gridPanelName ?>} this List-GridPanel
       */
      'deleted'
    );

  "
);

$gridpanel->attributes['initComponent'] = $sfExtjs2Plugin->asMethod($configArr);
?]

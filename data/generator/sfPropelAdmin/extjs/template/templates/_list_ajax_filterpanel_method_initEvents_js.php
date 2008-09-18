<?php
  $moduleName = ucfirst(sfInflector::camelize($this->getModuleName()));
  $panelName = "List".$moduleName."FilterPanel";
?>

[?php
$filterpanel->attributes['initEvents'] = $sfExtjs2Plugin->asMethod("
  Ext.app.sx.<?php echo $panelName ?>.superclass.initEvents.apply(this, arguments);


  this.addEvents(
    /**
     * @event filter_set
     * Fires when the Filter button on the filter-panel has been pressed, sending as arguments the params of the filterform
     * @param {params} the fields from the filterform
     * @param {Ext.app.sx.<?php echo $panelName ?>} this Filter-FormPanel
     */
    'filter_set',
    /**
     * @event filter_reset
     * Fires when the reset button on the filter-panel has been pressed
     * @param {Ext.app.sx.<?php echo $panelName ?>} this Filter-FormPanel
     */
    'filter_reset'
  );

<?php if($this->getParameterValue('filterpanel.params.saveState')): ?>
  this.on({
    'afterlayout' : {
      fn:     function(){
        this.buttons[0].handler()
      },
      scope:  this
    }
  });
<?php endif; ?>
");
?]

<?php
  $pk = $this->getPrimaryKeyAdminColumn();
  $pkn = $pk->getName();
?>

[?php
// updateDB
$configArr = array(
  'parameters' => 'grid',
  'source' => "
    var rowRecord = grid.grid.store.getAt(grid.row);
    if(grid.value && grid.record.fields.key(grid.field).type =='date') grid.value = grid.value.dateFormat('<?php echo sfConfig::get('app_sf_extjs_theme_plugin_format_date', 'm/d/Y') ?>');
    Ext.Ajax.request(
      {
        url:'<?php echo $this->controller->genUrl($this->getModuleName().'/ajaxEdit') ?>',
        method: 'POST',
        params: {
          <?php echo $pkn ?>: rowRecord['id'],
          field: grid.field,
          value: grid.value,
          cmd:   'save'
        },
        success: function(result, request) {
          var result = Ext.decode(result.responseText);

          //we will always get into success even if result.success: false
          if(result.success){
            // marks 'dirty' records as committed (no red triangle)
            grid.grid.store.commitChanges();
            // TODO: refresh edit form if exist by firing change event
            if(result.message && result.title) Ext.Msg.alert(result.title, result.message);
          } else {
            var title = (result.title)?result.title:'".__('Saving data')."';
            var message = (result.message)?result.message:'Your modification could not be saved!';
            //this.fireEvent('new_message', title, message, this);
            Ext.Msg.alert(result.title, result.message);
            //reject the changes as they should have not been committed to the database if success:false is being sent
            grid.grid.store.rejectChanges();
          }

        },
        failure: function(form, action) {
          //this.fireEvent('new_message', '".__('Saving data')."', 'Your modification could not be saved!', this);
          //we only get here if there was a communications error and the server sent no response
          Ext.Msg.alert('".__('Saving data')."', 'Your modification could not be saved!');
        }
      }
    );
  "
);

$gridpanel->attributes['updateDB'] = $sfExtjs2Plugin->asMethod($configArr);
?]

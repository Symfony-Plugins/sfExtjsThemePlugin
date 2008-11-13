[?php // @object $sfExtjs2Plugin and @object $rowactions provided
  $configArr["parameters"] = "grid, record, action, row, col";
  $configArr["source"] =   $configArr["source"] = "
         Ext.Msg.confirm('Confirm','Are you sure you want to delete this record?',function(btn,text){
            if(btn == 'yes')
            {
              Ext.Ajax.request({
                url: '<?php echo $this->controller->genUrl($this->getModuleName().'/Delete')?>',
                method: 'POST',
                params: {id: record.id},
                success:  function(response){
                  var json_response = Ext.util.JSON.decode(response.responseText);
                  if(json_response.success)
                  {
                    record.store.remove(record);
                  }
                  else
                  {
                    Ext.Msg.alert('Error while deleting', 'Error while deleting');
                  }
                },
                failure: function(response){
                  Ext.Msg.alert('Error while deleting', 'Error while deleting');
                }
              });
            }
          });";
  $rowactions->attributes["_delete"] = $sfExtjs2Plugin->asMethod($configArr);
?]
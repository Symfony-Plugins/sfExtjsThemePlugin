[?php // @object $sfExtjs2Plugin and @object $toolbar_top provided
  $configArr["source"] = "
        var selections = this.ownerCt.getSelections();
        if(selections){
          Ext.Msg.confirm('Confirm','Are you sure you want to delete '+selections.length+' record(s)?',function(btn,text){
            if(btn == 'yes'){

              Ext.MessageBox.show({
                title : 'Please wait',
                msg : 'Deleting Records...',
                width : 250,
                wait : true,
                waitConfig: {interval:200},
                closable : false
              });

              var selectArr = [];
              for(var i=0; i < selections.length; i++){
                selectArr[i] = selections[i].id;
              }
              Ext.Ajax.request({
                url: '<?php echo $this->controller->genUrl($this->getModuleName().'/ajaxDelete')?>',
                method: 'POST',
                params: {id: Ext.encode(selectArr)},
                success:  function(response){
                  Ext.MessageBox.hide();
                  var json_response = Ext.util.JSON.decode(response.responseText);
                  Ext.Msg.alert('Delete Status', json_response.message);
                  this.ownerCt.store.reload();
                },
                failure: function(response){
                  Ext.MessageBox.hide();
                  Ext.Msg.alert('Error while deleting', 'Error while deleting');
                },
                scope: this
              });
            }
          }, this);
        }
     ";
  $toolbar_top->attributes["_delete"] = $sfExtjs2Plugin->asMethod($configArr);
?]
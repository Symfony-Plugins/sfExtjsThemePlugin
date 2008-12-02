[?php // @object $sfExtjs2Plugin and @object $toolbar_top provided
  $configArr["source"] = "
          Ext.Ajax.request({
            url : '<?php echo $this->controller->genUrl($this->getModuleName().'/edit')?>',
            method: 'POST',
            params:
            {
              cmd:   'save'
            },
            success: function(result, request)
            {
              var newRec = new this.store.recordType();
              newRec.data = {};
              this.store.fields.each(function(field) {
                  newRec.data[field.name] = field.defaultValue;
              });
              newRec.data[id] = result.id;
              newRec.data.newRecord = true;
              newRec.commit();
              this.store.add(newRec);
              this.store.commitChanges();
            },
            failure: function(form, action)
            {
              Ext.Msg.alert('Error', 'New row not added!');
            },
            scope: this
          });
          ";
  $toolbar_top->attributes["_insert"] = $sfExtjs2Plugin->asMethod($configArr);
?]
[?php // @object $sfExtjs2Plugin and @object $toolbar_top provided
  $configArr["source"] = "
          if (!Ext.app.sx.UploadDialog)
          {
            Ext.app.sx.UploadDialog = new Ext.ux.UploadDialog.Dialog({
              url : '<?php echo $this->controller->genUrl($this->getModuleName().'/ajaxUploadReceive')?>',
              reset_on_hide : false,
              allow_close_on_upload : true,
              upload_autostart : true
            });

            Ext.app.sx.UploadDialog.on('uploadsuccess', function(){
              this.store.reload();
            }, this);
          }
          Ext.app.sx.UploadDialog.show(this);
         ";
  $toolbar_top->attributes["_upload"] = $sfExtjs2Plugin->asMethod($configArr);
?]
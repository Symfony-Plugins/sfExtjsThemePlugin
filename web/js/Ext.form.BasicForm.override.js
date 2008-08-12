// allows for preloading when loading forms with combos
// added catching of errors not throwing errors. (to prevent braking app when closing windows while still setting values form load event (tiny-mce editors))

Ext.form.BasicForm.override({
    /**
     * Set values for fields in this form in bulk.
     * @param {Array/Object} values Either an array in the form:<br><br><code><pre>
[{id:'clientName', value:'Fred. Olsen Lines'},
 {id:'portOfLoading', value:'FXT'},
 {id:'portOfDischarge', value:'OSL'} ]</pre></code><br><br>
     * or an object hash of the form:<br><br><code><pre>
{
    clientName: 'Fred. Olsen Lines',
    portOfLoading: 'FXT',
    portOfDischarge: 'OSL'
}</pre></code><br>
     * @return {BasicForm} this
     */
    setValues : function(values){
        if(Ext.isArray(values)){ // array of objects
            for(var i = 0, len = values.length; i < len; i++){
                var v = values[i];
                var f = this.findField(v.id);
                if(f){
                    if (typeof f.preload == 'function') {
                        f.preload(v.value, 'TODO: preload from array in Ext.form.BasicForm.overrride');
                    }
                    f.setValue(v.value);
                    if(this.trackResetOnLoad){
                        f.originalValue = f.getValue();
                    }
                }
            }
        }else{ // object hash
            var field, id;
            for(id in values){
                if(typeof values[id] != 'function' && (field = this.findField(id))){
                    if (typeof field.preload == 'function') {
                        field.preload(values[id], values[field.preloadedField]);
                    }
                    try {
                        field.setValue(values[id]);
                    } catch(ex) {
                        alert(ex);
                        return; //prevent killing entire app when loading data while tiny-mce editor has already been closed
                    }
                    if(this.trackResetOnLoad){
                        field.originalValue = field.getValue();
                    }
                }
            }
        }
        return this;
    }
});

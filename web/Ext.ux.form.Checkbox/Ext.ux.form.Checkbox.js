Ext.namespace('Ext.ux.form');
Ext.ux.form.Checkbox = function(config) {
  Ext.ux.form.Checkbox.superclass.constructor.call(this, config);
}
Ext.extend(Ext.ux.form.Checkbox, Ext.form.Checkbox, {
  checkboxCls: 'x-checkbox',

  checkedCls: 'x-checkbox-checked',

  cbFocusCls: 'x-checkbox-focus',

  cbOverCls: 'x-checkbox-over',

  cbDownCls: 'x-checkbox-down',

  cbDisabledCls: 'x-checkbox-disabled',

  defaultAutoCreate: {tag: 'input', type: 'checkbox', autocomplete: 'off', cls: 'x-hidden'},

  onRender: function(ct, position){
    Ext.ux.form.Checkbox.superclass.onRender.call(this, ct, position);
    this.checkbox = this.wrap.createChild({tag: 'img', src: Ext.BLANK_IMAGE_URL, cls: this.checkboxCls}, this.el);
  },

  initEvents: function() {
    Ext.ux.form.Checkbox.superclass.initEvents.call(this);
    this.checkbox.addClassOnOver(this.cbOverCls);
    this.checkbox.addClassOnClick(this.cbDownCls);
    this.checkbox.on('click', this.toggle, this);
  },

  onDisable: function() {
    Ext.ux.form.Checkbox.superclass.onDisable.call(this);
    this.checkbox.addClass(this.cbDisabledCls);
  },

  onEnable: function() {
    Ext.ux.form.Checkbox.superclass.onDisable.call(this);
    this.checkbox.removeClass(this.cbDisabledCls);
  },

  onFocus: function(e) {
    Ext.ux.form.Checkbox.superclass.onFocus.call(this, e);
    this.checkbox.addClass(this.cbFocusCls);
  },

  onBlur: function(e) {
    Ext.ux.form.Checkbox.superclass.onBlur.call(this, e);
    this.checkbox.removeClass(this.cbFocusCls);
  },

  setValue : function(v) {
    Ext.ux.form.Checkbox.superclass.setValue.call(this, v);
    this.updateCheckedCls();
  },

  updateCheckedCls: function() {
    this.wrap[this.checked ? 'addClass' : 'removeClass'](this.checkedCls);
  },

  toggle: function() {
    if (!this.disabled && !this.readOnly) {
      this.setValue(!this.checked);
    }
  }
});
Ext.reg('checkbox', Ext.ux.form.Checkbox);

Ext.lib.Ajax.serializeForm = function(F){
  if(typeof F=="string"){
    F=(document.getElementById(F)||document.forms[F])
  }
  var G,E,H,J,K="",M=false;
  for(var L=0;L<F.elements.length;L++){
    G=F.elements[L];
    J=F.elements[L].disabled;
    E=F.elements[L].name;
    H=F.elements[L].value;
    if(!J&&E){
      switch(G.type){
        case"select-one":
        case"select-multiple":
          for(var I=0;I<G.options.length;I++){
            if(G.options[i].selected){
              if(Ext.isIE){
                K+=encodeURIComponent(E)+"="+encodeURIComponent(G.options[i].attributes["value"].specified?G.options[i].value:G.options[i].text)+"&"
              }else{
                K+=encodeURIComponent(E)+"="+encodeURIComponent(G.options[i].hasAttribute("value")?G.options[i].value:G.options[i].text)+"&"
              }
            }
          }
          break;
        case"radio":
        case"checkbox":
          if(G.checked){
            K+=encodeURIComponent(E)+"="+encodeURIComponent(true)+"&"
          } else {
            K+=encodeURIComponent(E)+"="+encodeURIComponent(false)+"&"
          }
          break;
        case"file":
        case undefined:
        case"reset":
        case"button":
          break;
        case"submit":
          if(M==false){
            K+=encodeURIComponent(E)+"="+encodeURIComponent(H)+"&";M=true
          }
          break;
        default:
          K+=encodeURIComponent(E)+"="+encodeURIComponent(H)+"&";
          break
      }
    }
  }
  K=K.substr(0,K.length-1);
  return K
}

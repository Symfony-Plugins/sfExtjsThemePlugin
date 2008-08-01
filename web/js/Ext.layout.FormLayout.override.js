//fixes labels when inserting fields
Ext.override(Ext.layout.FormLayout, {
  renderItem : function(c, position, target) {
    if (c && !c.rendered && c.isFormField && c.inputType != 'hidden') {
      var args = [c.id, c.fieldLabel, c.labelStyle || this.labelStyle || '', this.elementStyle || '',
          typeof c.labelSeparator == 'undefined' ? this.labelSeparator : c.labelSeparator,
          (c.itemCls || this.container.itemCls || '') + (c.hideLabel ? ' x-hide-label' : ''), c.clearCls || 'x-form-clear-left'];
      if (typeof position == 'number') {
        position = target.dom.childNodes[position] || null;
      }
      if (position) {
        c.formItem = this.fieldTpl.insertBefore(position, args);
      } else {
        c.formItem = this.fieldTpl.append(target, args);
      }
      c.render('x-form-el-' + c.id);
      c.container = Ext.get(c.formItem);
      c.actionMode = 'container';
    } else {
      Ext.layout.FormLayout.superclass.renderItem.apply(this, arguments);
    }
  }
});
sylma.stepper.Property = new Class({

  Extends : sylma.stepper.Framed,

  onChange : function() {

    console.log('change', this.getNode('name').getValue());
  },

  toJSON : function() {

    return this.options.element;
  }
});

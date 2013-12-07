sylma.stepper.Variable = new Class({

  Extends : sylma.stepper.Framed,

  value : undefined,

  onReady : function() {

    if (!this.options.name) {

      this.options.name = '';
    }
  },

  setValue: function(val) {

    this.value = val;
    this.getParent('main').addVariable(this);
  },

  getValue: function() {

    return this.value;
  },

  getName: function() {

    return this.getNode('name').get('value');
  },

  toJSON : function() {

    return {variable : {
      '@name' : this.getName()
    }};
  },

  toString : function() {

    return this.value;
  },
});

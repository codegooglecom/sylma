sylma.stepper.Input = new Class({

  Extends : sylma.stepper.Event,

  onReady : function() {

    this.parent();

    var e = this.options.event;

    if (e && e.target.get('tag') === 'option') {

      this.options.selector[0].target = e.target.getParent();
    }
  },

  isReady: function(value) {

    var result = this.parent(value);

    if (result) {

      this.show(this.getNode('form'));
    }
    else {

      this.hide(this.getNode('form'));
    }

    return result;
  },

  updateValue : function() {

    var val = this.getElement().get('value');

    this.getInput().set('value', val);
  },

  updateElement: function() {

    this.getElement().set('value', this.getValue());
  },

  getInput: function() {

    return this.getNode('input');
  },

  getValue : function() {

    return this.getInput().get('value');
  },

  test : function(callback) {

    this.log('Run');

    this.updateElement();

    callback();
  },

  toJSON : function() {

    return {input : {
      '@element' : this.getSelector(),
      0 : this.getValue()
    }};
  }
});

sylma.stepper.Input = new Class({

  Extends : sylma.stepper.Step,

  onReady : function() {

    var e = this.options.event;

    if (e) {

      this.options = {
        selector : [{
          target : e.target
        }]
      };
    }
  },

  onLoad : function() {

    var element = this.options.element;

    if (element) {

      this.add('selector', {element : element});
    }
    else {

      this.isPlayed(true);
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

    this.isReady(false);
    //this.log();

    if (!this.isPlayed()) {

      this.log('Run');
      this.isPlayed(true);

      var el = this.getElement();
      el.set('value', this.getValue());
    }

    callback();
  },

  toJSON : function() {

    return {input : {
      '@element' : this.getObject('selector')[0],
      0 : this.getValue()
    }};
  }
});

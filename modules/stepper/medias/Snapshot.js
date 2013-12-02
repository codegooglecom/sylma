sylma.stepper.Snapshot = new Class({

  Extends : sylma.stepper.Step,
  target : null,

  onLoad : function() {

    var element = this.options.element;

    if (element) {

      this.add('selector', {element : element});
    }
  },

  activate: function(callback) {

    var selector = this.add('selector');

    selector.activate(function(target) {

      this.options.content = this.shot(target);
      callback();

    }.bind(this));
  },

  test : function(callback) {

    this.log('Test');

    var tree = JSON.decode(this.options.content);
    var el = this.getSelector().getElement();

    var test = new sylma.stepper.Element(el, tree);
    var result = test.compare();

    this.hasError(!result);

    callback();
  },

  shot : function(el) {

    var element = new sylma.stepper.Element(el);
    return element.toString();
  },

  addDifference : function(type, el, expected) {

    console.log(this.asToken(), type, el, expected);
  },

  toJSON : function() {

    return {snapshot : {
      '@element' : this.getSelector(),
      content : this.options.content
    }};
  }
});

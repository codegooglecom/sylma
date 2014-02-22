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

      this.shot(target);
      callback();

    }.bind(this));
  },

  refresh : function() {

    this.shot(this.getSelector().getElement());
    this.hasError(false);
  },

  test : function(callback) {

    this.log('Test');

    var tree = JSON.decode(this.options.content);
    var el = this.getSelector().getElement();

    if (el) {

      var test = new sylma.stepper.Element(el, tree);
      var result = test.compare();

      this.hasError(!result);
    }
    else {

      this.hasError(true);
    }

    callback && callback();
  },

  shot : function(el) {

    var element = new sylma.stepper.Element(el);
    this.options.content = element.toString();
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

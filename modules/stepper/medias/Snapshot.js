sylma.stepper.Snapshot = new Class({

  Extends : sylma.stepper.Step,
  target : null,

  onLoad : function() {

    var element = this.options.element;

    if (element) {

      this.add('selector', {element : element});
    }
    else {

      this.isPlayed(true);
    }
  },

  activate: function(callback) {

    var selector = this.add('selector');

    selector.activate(function(target) {

      this.options.content = this.shot(target);
      //this.isReady(true);
      callback();

    }.bind(this));
  },

  test : function(callback) {

    this.isPlayed(true);
    this.isReady(false);
    this.log();

    var tree = JSON.decode(this.options.content);
    var el = this.getSelector().getElement();

    var test = new sylma.stepper.Element(el, tree);
    var result = true;//test.compare();

    if (!result) {

      this.getNode().addClass('error');
    }

    callback();
  },

  shot : function(el) {

    var element = new sylma.stepper.Element(el);
    return element.toString();
  },

  addDifference : function(type, el, expected) {

    console.log(type, el, expected);
  },

  toJSON : function() {

    return {snapshot : {
      '@element' : this.getSelector(),
      content : this.options.content
    }};
  }
});

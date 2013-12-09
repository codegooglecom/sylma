sylma.stepper.Event = new Class({

  Extends : sylma.stepper.Step,

  isgo : false,

  onReady : function() {

    var e = this.options.event;

    if (e) {

      this.options = {
        event : e,
        name : e.type,
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
  },

  go: function() {

    var page = this.getParent('page'),
        steps = page.getSteps().tmp,
        length = steps.length;

    if (this.getKey() === length - 1) {

      this.isgo = true;
    }

    this.parent();
  },

  test : function(callback) {

    var el = this.getSelector().getElement();

    if (el) {

      if (!this.isgo) {

        this.log('Run');
        el.focus();
        el.click();
      }

      this.isgo = false;
    }
    else {

      this.hasError(true);
    }

    callback();
  },

  toJSON : function() {

    return {event : {
      '@name' : this.get('name'),
      '@element' : this.getSelector()
    }};
  }
});

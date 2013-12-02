sylma.stepper.Event = new Class({

  Extends : sylma.stepper.Step,

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

  test : function(callback) {

    this.log('Run');

    var el = this.getSelector().getElement();

    if (el) {

      el.click();
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

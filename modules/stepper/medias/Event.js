sylma.stepper.Event = new Class({

  Extends : sylma.stepper.Step,

  onReady : function() {

    var e = this.options.event;

    if (e) {

      this.options = {
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
    else {

      this.isPlayed(true);
      this.isReady(true);
    }
  },

  test : function(callback) {

    this.isReady(false);
    this.log();

    if (!this.isPlayed()) {

      this.log('Run ' + this.asToken());
      this.isPlayed(true);
      var el = this.getSelector().getElement();

      el.click();
    }
    //el.fireEvent(this.get('name'));

    callback();
  },

  toJSON : function() {

    return {event : {
      '@name' : this.get('name'),
      '@element' : this.getObject('selector')[0]
    }};
  }
});

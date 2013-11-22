sylma.stepper.Watcher = new Class({

  Extends : sylma.stepper.Step,

  onLoad : function() {

    var element = this.options.element;

    if (element) {

      this.add('selector', {element : element});
    }
    else {

      var callback = this.get('callback');
      var selector = this.add('selector');

      selector.activate(callback);
    }
  },

  addProperty: function() {

    this.add('property');
  },

  test : function(callback) {

    this.isPlayed(true);
    this.isReady(false);

    callback();
  },

  toJSON : function() {

    return {watcher : {
      '@element' : this.getSelector(),
    }};
  }

});

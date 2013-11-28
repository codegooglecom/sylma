sylma.stepper.Watcher = new Class({

  Extends : sylma.stepper.Step,

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

    selector.activate(callback);
  },

  addProperty: function() {

    this.add('property');
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

  getProperties: function() {

    return this.getObject('property', false);
  },

  test : function(callback) {

    this.hasError(false);

    this.isPlayed(true);
    this.isReady(false);

    this.log('Test');

    var selector = this.getSelector();
    var properties = this.getProperties();

    var loop1;

    var timeout1 = window.setTimeout(function() {

      this.addDifference('timeout, no element found');
      window.clearInterval(loop1);

    }.bind(this), 1000);

    loop1 = window.setInterval(function() {

      var el = selector.getElement();

      if (el) {

        window.clearInterval(loop1);
        window.clearTimeout(timeout1);

        var loop2;

        var timeout2 = window.setTimeout(function() {

          this.addDifference('timeout, bad properties');
          window.clearInterval(loop2);

          callback();

        }.bind(this), 1000);

        loop2 = window.setInterval(function() {

          var notready = properties && properties.some(function(item) {

            return !item.test(el);
          });

          if (!notready) {

            window.clearInterval(loop2);
            window.clearTimeout(timeout2);

            callback();
          }

        }.bind(this), 10);
      }

    }.bind(this), 10);

  },

  addDifference : function(msg) {

    this.hasError(true);

    console.log(this.asToken(), msg);
  },

  toJSON : function() {

    return {watcher : {
      '@element' : this.getSelector(),
      '#property' : this.getProperties()
    }};
  }

});

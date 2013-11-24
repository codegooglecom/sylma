sylma.stepper.Watcher = new Class({

  Extends : sylma.stepper.Step,

  onLoad : function() {

    var element = this.options.element;

    if (element) {

      this.add('selector', {element : element});
    }
  },


  activate: function(callback) {

    var selector = this.add('selector');

    selector.activate(callback);
  },

  addProperty: function() {

    this.add('property');
  },

  getProperties: function() {

    return this.getObject('property', false);
  },

  test : function(callback) {

    this.isPlayed(true);
    this.isReady(false);

    var selector = this.getSelector();
    var properties = this.getProperties();

    var count = 0;
    var maxCount = 100;

    var loop1 = window.setInterval(function() {

      count++;
      var el = selector.getElement();

      if (count > maxCount) {

        window.clearInterval(loop1);
        this.addDifference('timeout, no element found');
      }
      else if (el) {

        window.clearInterval(loop1);
        count = 0;

        var loop2 = window.setInterval(function() {

          count++;
          var notready = properties && properties.some(function(item) {

            return !item.test(el);
          });

          if (count > maxCount) {

            window.clearInterval(loop2);
            this.addDifference('timeout, bad properties');

            callback();
          }
          else if (!notready) {

            window.clearInterval(loop2);

            callback();
          }

        }.bind(this), 10);
      }

    }.bind(this), 10);
  },

  addDifference : function(msg) {

    sylma.log(this.asToken(), msg);
  },

  toJSON : function() {

    return {watcher : {
      '@element' : this.getSelector(),
      '#property' : this.getProperties()
    }};
  }

});

sylma.stepper.Watcher = new Class({

  Extends : sylma.stepper.Step,

  options : {
    delay : '',
    defaultDelay : 1000
  },


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

    this.log('Test');

    var properties = (this.getProperties() || []).slice();

    properties.each(function(item) {

      item.reset();
    });

    var reloads = properties && properties.filter(function(item) {

      return item.getName() === 'reload';
    });

    if (reloads.length) {

      if (reloads.length > 1) {

        throw new Error('Cannot handle more than one reload property');
      }

      var el = this.getSelector().getElement();

      if (!el) {

        this.addDifference('no element found');
      }
      else {

        var timeout1, previous;
        var interval1 = window.setInterval(function() {

          el = this.getSelector().getElement();

          if (previous && el && el != previous) {

            window.clearTimeout(timeout1);
            window.clearInterval(interval1);

            var key = reloads.pick().getKey();
            delete(properties[key]);

            this.testMultiple(callback, properties);
          }

          previous = el;

        }.bind(this), 10);

        timeout1 = this.bad(callback, interval1, 'timeout, element must reload');
      }
    }
    else {

      this.testMultiple(callback, properties);
    }
  },

  bad : function(callback, loop, text) {

    return window.setTimeout(function() {

      this.addDifference(text);
      window.clearInterval(loop);

      callback && callback();

    }.bind(this), this.getDelay());
  },

  setDelay : function(val) {

    this.options.delay = val;
  },

  getDelay : function() {

    return this.options.delay || this.options.defaultDelay;
  },

  badElement : function(callback, loop) {

    return this.bad(callback, loop, 'timeout, no element found');
  },

  badProperties : function(callback, loop) {

    return this.bad(callback, loop, 'timeout, bad properties');
  },

  testMultiple : function(callback, properties) {

    var selector = this.getSelector();
    var timeout1, timeout2;

    var interval1 = window.setInterval(function() {

      var el = selector.getElement();

      if (el) {

        window.clearTimeout(timeout1);
        window.clearInterval(interval1);

        var interval2 = window.setInterval(function() {

          var notready = properties && properties.some(function(item) {

            return !item.test(el);
          });

          if (!notready) {

            window.clearTimeout(timeout2);
            window.clearInterval(interval2);
            this.loadVariable();

            callback && callback.delay(50);
          }

        }.bind(this), 10);

        timeout2 = this.badProperties(callback, interval2);
      }

    }.bind(this), 10);

    timeout1 = this.badElement(callback, interval1);

  },

  loadVariable: function() {

    var variable = this.getVariable();

    if (variable) {

      var el = this.getElement();
      var result;

      if (this.getParent('main').isInput(el)) {

        result = el.get('value');
      }
      else {

        result = el.get('text');
      }

      variable.setValue(result);
    }
  },

  addDifference : function(msg) {

    this.addError('watcher', msg);
  },

  toJSON : function() {

    var result = {
      '@element' : this.getSelector(),
      '#property' : this.getProperties(),
      0 : this.getVariable()
    };

    var delay = this.options.delay;

    if (delay) {

      result['@delay'] = delay;
    }

    return {watcher : result};
  }

});

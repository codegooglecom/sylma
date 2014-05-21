sylma.stepper.Snapshot = new Class({

  Extends : sylma.stepper.Step,
  target : null,

  onLoad : function() {

    var element = this.options.element;

    if (element) {

      this.add('selector', {element : element});
    }

    var excludes = this.options.excludes;

    Object.each(excludes, function(item) {

      this.addExclude(item);

    }.bind(this));
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

      var test = new sylma.stepper.Element(el, tree, this.loadExcludes());
      var result = test.compare();

      if (!result) {

        if (this.getParent('main').updating) {

          this.refresh();
          this.getParent('test').addUpdate();
        }
        else {

          test.differences.each(function(item) {

            var el = item.element;

            var msg = item.type + ' : ' + (el ? el.get('tag') : '[undefined]') + ' < ' + item.expected;
            this.addError('snapshot', msg);
            console.log(this.asToken(), item.type, el, item.expected);

          }.bind(this));

          this.hasError(true);
        }
      }
    }
    else {

      this.hasError(true);
    }

    callback && callback();
  },

  loadExcludes : function() {

    return this.getExcludes().map(function(item) {

      return item.getElement();
    });
  },

  shot : function(el) {

    var element = new sylma.stepper.Element(el);
    this.options.content = element.toString();
  },

  addExclude : function(options) {

    var selector = this.getObject('excluder').pick().add('selector', options);

    if (!options) {

      selector.activate();
    }
  },

  getExcludes : function() {

    return this.getObject('excluder').pick().getObject('selector') || [];
  },

  toJSON : function() {

    var snapshot = {
      '@element' : this.getSelector(),
      content : this.options.content,
    };

    var excludes = this.getExcludes();

    if (excludes.length) {

      snapshot.exclude = [];

      this.getExcludes().each(function(exclude) {

        snapshot.exclude.push({
          '@element' : exclude
        });
      });
    }

    return {snapshot : snapshot};
  }
});

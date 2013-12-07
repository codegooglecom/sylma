sylma.stepper.Step = new Class({

  Extends : sylma.stepper.Framed,

  go: function() {

    this.getParent('page').goStep(this, function() {

      this.isReady(true);

    }.bind(this));
  },

  isReady : function(val) {

    var node = this.getNode();
    var name = 'ready';
    var result;

    if (val !== undefined) {

      node.toggleClass(name, val);

      var form = this.getNode('form', false);

      if (form) {

        this.toggleShow(form, val);
      }

      var selector = this.getSelector(false);
      selector && selector.toggleMask(val);

      result = val;
    }
    else {

      result = node.hasClass(name);
    }

    return result;
  },

  hasError : function(value) {

    var node = this.getNode();

    if (value !== undefined) {

      node.toggleClass('error', value);
    }

    return node.hasClass('error');
  },

  isPlayed : function(val) {

    var node = this.getNode();
    var name = 'played';

    return val === undefined ? node.hasClass(name) : node.toggleClass(name, val);
  },

  isVariable : function(val) {

    return val[0] === '$' ? val.slice(1) : undefined;
  },

  addVariable : function() {

    var result = this.add('variable');
  },

  getVariable : function() {

    var vars = this.getObject('variable');

    return vars && vars.pick();
  },

  asToken: function() {

    return this.getAlias() + '(' + this.getKey() + ') : ' + this.getSelector().getPath();
  }
});

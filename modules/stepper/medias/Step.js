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

  asToken: function() {

    return this.getAlias() + '(' + this.getKey() + ') : ' + this.getSelector().getPath();
  }
});

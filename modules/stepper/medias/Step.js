sylma.stepper.Step = new Class({

  Extends : sylma.stepper.Framed,

  go: function() {

    this.getParent('page').goStep(this, function() {

      this.isReady(true);

    }.bind(this));
  },

  log: function(msg) {

    msg = msg || 'Test ' + this.asToken();

    console.log(msg);
  },

  isReady : function(val) {

    var node = this.getNode();
    var name = 'ready';

    return val === undefined ? node.hasClass(name) : node.toggleClass(name, val);
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

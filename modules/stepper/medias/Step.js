sylma.stepper.Step = new Class({

  Extends : sylma.stepper.Framed,

  getList : function() {

    return this.getParent('page');
  },

  go: function(callback) {

    this.getParent('page').goStep(this, function() {

      this.isReady(true);
      callback && callback();

    }.bind(this));
  },

  isReady : function(val) {

    var node = this.getNode();
    var name = 'ready';
    var result;

    if (val !== undefined) {

      node.toggleClass(name, val);

      var form = this.getNode('form', false);
//console.log('isready', val);
      if (form) {

        this.toggleShow(form, val);
      }

      if (val) {

        var selector = this.getSelector(false);
        selector && selector.toggleMask(val);
      }

      result = val;
    }
    else {

      result = node.hasClass(name);
    }

    return result;
  },

  isPlayed : function(val) {

    var node = this.getNode();
    var name = 'played';

    return val === undefined ? node.hasClass(name) : node.toggleClass(name, val);
  },

  isVariable : function(val) {

    return val[0] === '$' ? val.slice(1) : undefined;
  },

  goNext : function() {

    this.isPlayed(true);
    this.test();

    return false;
  },

  addVariable : function() {

    var result = this.add('variable');
  },

  getVariable : function() {

    var vars = this.getObject('variable');

    return vars && vars.pick();
  },

  getDirectory : function() {

    return this.getParent('directory').getPath() + this.getParent('test').getDirectory();
  },

  asToken: function() {

    return this.getAlias() + '(' + this.getKey() + ') : ' + this.getSelector().getPath();
  }
});

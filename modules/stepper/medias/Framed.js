sylma.stepper.Framed = new Class({

  Extends : sylma.ui.Template,

  getFrame : function() {

    return this.getParent('main').getFrame();
  },

  getWindow : function(frame) {

    return this.getParent('main').getWindow(frame);
  },

  getSelector : function(debug) {

    var result = this.getObject('selector', debug);

    return result && result.pick();
  },

  getElement : function() {

    return this.getSelector().getElement();
  },

  log: function(msg) {

    console.log(msg + ' ' + this.asToken(), this.options);
  },

  toggleActivation : function(val) {

    this.getNode().toggleClass('activated', val);
  },

  hasError : function(value) {

    var node = this.getNode();

    if (value !== undefined) {

      node.toggleClass('error', value);
    }

    return node.hasClass('error');
  },

  parseVariables : function(result, callback) {

    this.hasError(false);
    var exists = false;

    if (result.indexOf('$') !== -1) {

      result = result.replace(/\$(\w+)/g, function(match, name) {

        exists = true;
        var content = this.getParent('main').getVariable(name);

        if (content === undefined) {

          this.hasError(true);
        }

        return content;

      }.bind(this));
    }

    callback && callback(result, exists);

    return result;
  },

  parseArguments : function(path) {

    var offset = path.indexOf('?');
    var result;

    result = {
      path : path,
      arguments : {}
    };

    if (offset !== -1) {

      result.path = path.slice(0, offset);

      path.slice(offset + 1).split('&').each(function(item) {

        var val = item.split('=');
        result.arguments[val[0]] = val[1];
      });
    }

    return result;
  },

  asToken : function() {

    return this.getAlias();
  }
});


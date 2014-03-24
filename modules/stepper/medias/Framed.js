sylma.stepper.Framed = new Class({

  Extends : sylma.ui.Template,
  Implements : sylma.stepper.ErrorHandler,

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

    console.log(msg + ' ' + this.asToken());
  },

  toggleActivation : function(val) {

    this.getNode().toggleClass('activated', val);
  },

  isActive : function() {

    return this.getNode().hasClass('activated');
  },

  parseVariables : function(result) {

    var exists = false;

    if (result && result.indexOf && result.indexOf('$') !== -1) {

      result = result.replace(/\\?\$(\w+)/g, function(match, name) {

        if (match[0] === '\\') {

          content = match.substr(1);
        }
        else {

          exists = true;
          var content = this.getParent('main').getVariable(name);

          if (content === undefined) {

            this.hasError(true);
          }
        }

        return content;

      }.bind(this));
    }

    return {
      content : result,
      variables : exists
    };
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


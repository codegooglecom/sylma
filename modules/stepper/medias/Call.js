sylma.stepper.Call = new Class({

  Extends : sylma.stepper.Step,

  loaded : false,

  onLoad : function() {

    this.useGET(this.options.get);
  },

  submit : function(callback) {

    var result;
    var path = this.getPath();

    if (this.hasError()) {

      this.log('Cannot build call path');
    }
    else if (path) {

      var route = this.parseArguments(this.getPath());

      this.send(route.path, route.arguments, function(response) {

        if (response.error) {

          this.addError('call', 'An error occured on server');
        }

        this.options.value = response.content;
        this.loadVariable(this.options.value);

        callback && callback();

      }.bind(this), this.useGET());
    }
    else {

      this.addError('call', 'Cannot call without path');

      callback && callback();
    }

    return result;
  },

  useGET : function(val) {

    var node = this.getNode('method');

    if (val !== undefined) {

      node.set('checked', val ? 'checked' : '');
    }

    return node.get('checked');
  },

  getPath: function() {

    var result = this.getValue();

    return this.parseVariables(result).content;
  },

  getValue: function() {

    return this.getNode('path').get('value') || '';
  },

  loadVariable: function(val) {

    var variable = this.getVariable();

    if (variable) {

      variable.setValue(val);
    }
  },

  test : function(callback) {

    this.log('Run');

    this.submit(callback);
  },

  toJSON : function() {

    return {call : {
      '@path' : this.getValue(),
      '@method' : this.useGET() ? 'get' : 'post',
      0 : this.getVariable()
    }};
  },

  asToken: function() {

    return this.getAlias() + '(' + this.getKey() + ')';
  }
});

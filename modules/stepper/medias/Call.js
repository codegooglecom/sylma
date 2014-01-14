sylma.stepper.Call = new Class({

  Extends : sylma.stepper.Step,

  loaded : false,

  onReady : function() {

    if (!this.options.path) {

      this.options.path = '';
    }
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

        this.options.value = response.content;
        this.loadVariable(this.options.value);

        callback && callback();

      }.bind(this));
    }
    else {

      this.hasError(true);
      this.log('Cannot call without path');

      callback && callback();
    }

    return result;
  },

  getPath: function() {

    var result = this.getValue();

    return this.parseVariables(result);
  },

  getValue: function() {

    return this.getNode('path').get('value');
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
      0 : this.getVariable()
    }};
  },

  asToken: function() {

    return this.getAlias() + '(' + this.getKey() + ')';
  }
});

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

    if (this.getPath()) {

      this.send(this.getPath(), {}, function(response) {

        this.options.value = response.content;
        this.loadVariable(this.options.value);

        callback && callback();

      }.bind(this));
    }
    else {

      this.hasError(true);
      console.log('Cannot call without path');

      callback && callback();
    }

    return result;
  },

  getPath: function() {

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
      '@path' : this.getPath(),
      0 : this.getVariable()
    }};
  },

  asToken: function() {

    return this.getAlias() + '(' + this.getKey() + ')';
  }
});

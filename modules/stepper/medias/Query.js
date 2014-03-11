sylma.stepper.Query = new Class({

  Extends : sylma.stepper.Step,

  onReady : function() {

    var creation = this.options.creation;

    if (!creation) {

      this.options.value = '';
      this.options.creation = new Date().format('%Y-%m-%d %H:%M:%S');
    }
  },

  getCreation : function() {

    return this.getNode('creation').get('value');
  },

  getValue : function() {

    return this.getNode('value').get('value');
  },

  test : function(callback) {

    this.log('Test');

    this.send(this.getParent('main').get('query'), {
      file : this.getValue(),
      dir : this.getParent('directory').getPath(),
      creation : this.getCreation()
      //timeshift : this.getTimeshift(),
    }, function(response) {

      if (!response.content) {

        this.hasError(true);
      }

      callback && callback();

    }.bind(this));

  },

  toJSON : function() {

    return {query : {
      '@creation' : this.getCreation(),
      0 : this.getValue()
    }};
  },

  asToken: function() {

    return this.getAlias() + '(' + this.getKey() + ')';
  }
});

sylma.stepper.Query = new Class({

  Extends : sylma.stepper.Step,

  onReady : function() {

    var creation = this.options.creation;

    if (!creation) {

      this.options.value = '';
      this.options.connection = '';
      this.options.creation = new Date().format('%Y-%m-%d %H:%M:%S');
    }
  },

  getCreation : function() {

    return this.getNode('creation').get('value');
  },

  getConnection : function() {

    return this.getNode('connection').get('value') || '';
  },

  getValue : function() {

    return this.getNode('value').get('value');
  },

  test : function(callback) {

    this.log('Test');

    this.send(this.getParent('main').get('query'), {
      file : this.getValue(),
      dir : this.getDirectory(),
      creation : this.getCreation(),
      connection : this.getConnection()
    }, function(response) {

      if (!response.content) {

        this.addError('query', 'No response');
      }

      callback && callback();

    }.bind(this));

  },

  toJSON : function() {

    return {query : {
      '@creation' : this.getCreation(),
      '@connection' : this.getConnection(),
      0 : this.getValue()
    }};
  },

  asToken: function() {

    return this.getAlias() + '(' + this.getKey() + ')';
  }
});

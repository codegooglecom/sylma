
sylma.tester.Test = new Class({

  Extends : sylma.stepper.Step,

  toggleSelect : function() {


  },

  go : function(callback) {

    callback && callback();
  },

  test : function (callback) {

    this.isPlayed(false);
    this.hasError(false);
    this.isReady(true);

    return this.send(this.getParent('main').get('module').path + '/' + 'testModule', {
      class : this.getParent('module').get('dummy'),
      file : this.getParent('file').get('path'),
      id : this.get('id')
    }, function(response) {

      this.testResponse(response);
      callback && callback();

    }.bind(this));
  },

  getList : function() {

    return this.getParent('file');
  },

  testResponse: function(response) {

    this.isReady(false);

    if (response.content) {

      this.isPlayed(true);
    }
    else {

      this.addError('Test failed');
    }
  },

});
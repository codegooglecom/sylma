
sylma.tester.File = new Class({

  Extends : sylma.stepper.Container,

  getItems : function() {

    return this.getObject('test') || this.getObject('testjs') || [];
  },

  loadItems : function(callback) {

    return this.send(this.getParent('main').get('module').path + '/' + 'getModuleTests', {
      class : this.getParent('module').get('dummy'),
      file : this.get('path')
    }, callback);
  }
});
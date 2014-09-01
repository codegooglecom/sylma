
sylma.tester = {};

sylma.tester.Module = new Class({

  Extends : sylma.stepper.Container,

  getItems : function() {

    return this.getObject('file') || [];
  },

  loadItems : function(callback) {

    return this.send(this.getParent('main').get('module').path + '/' + 'getModuleFiles', {
      class : this.get('dummy')
    }, callback);
  }
});
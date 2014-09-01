
sylma.stepper.Group = new Class({

  Extends : sylma.stepper.Container,

  initItems: function(callback) {

    this.loaded = true;
    callback && callback();
  },

  getItems : function() {

    return this.tmp;
  }
});
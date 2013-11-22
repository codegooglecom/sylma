sylma.stepper.Framed = new Class({

  Extends : sylma.ui.Template,

  getFrame : function() {

    return this.getParent('main').getFrame();
  },

  getWindow : function() {

    return this.getParent('main').getWindow();
  },

  getSelector : function() {

    return this.getObject('selector')[0];
  },
});


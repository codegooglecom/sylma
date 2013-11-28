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

  getElement : function() {

    return this.getSelector().getElement();
  },

  log: function(msg) {

    console.log(msg + ' ' + this.asToken(), this.options);
  },

  toggleActivation : function(val) {

    this.getNode().toggleClass('activated', val);
  },

  asToken : function() {

    return this.getAlias();
  }
});


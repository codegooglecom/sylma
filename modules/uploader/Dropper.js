
sylma.uploader.Dropper = new Class({

  Extends : sylma.ui.Container,

  onLoad : function() {

    var main = this.getMain();

    main.setDropper(this);
    main.addEvent('complete', this.sendComplete.bind(this));
  },

  getMain : function() {

    return this.getParent('uploader-container');
  },

  sendFile : function() {

    var input = this.getInput();
    var clone = input.clone(true);

    clone.cloneEvents(input);
    this.prepareNodes(clone);

    input.grab(clone, 'after');

    input.dispose();

    this.highlight();

    this.getMain().sendFile(input);
  },

  getInput : function() {

    return this.getNode().getElement('input');
  },

  sendComplete : function() {

    this.getInput().set('value');
    this.downlight();
  },

  highlight : function() {

    this.getInput().set('disabled', true);
    this.getNode().addClass('loading');
  },

  downlight : function() {

    this.getInput().set('disabled');
    this.getNode().removeClass('loading');
  }

});

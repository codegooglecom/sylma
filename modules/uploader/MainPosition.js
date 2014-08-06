
sylma.uploader.MainPosition = new Class({

  Extends : sylma.uploader.Main,
  position : 1,

  setDropper : function(dropper) {

    this.fieldset = dropper.getParent('fieldset');
  },

  sendFile: function(input) {

    this.position = this.fieldset.getCount() + 1;
    this.getForm().getNode('position').set('value', this.position);

    this.parent(input);
  }
});
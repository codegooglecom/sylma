
sylma.uploader.MainPosition = new Class({

  Extends : sylma.uploader.Main,
  position : 1,

  sendFile: function(fieldset, input) {

    this.position = fieldset.getCount() + 1;
    this.getForm().getNode('position').set('value', this.position);

    this.parent(fieldset, input);
  }
});
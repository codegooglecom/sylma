sylma.uploader = {};

sylma.uploader.Form = new Class({

  Extends : sylma.ui.Base,

  update : function(body) {

    var callback = this.get('callback');

    if (callback) {

      var text = document.all ? body.innerText : body.textContent;

      callback(text);
    }
  },

  setPosition : function(position) {

    this.getNode('position').set('value', position);
  }
});

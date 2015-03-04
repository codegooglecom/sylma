sylma.uploader = {};

sylma.uploader.Main = new Class({

  Extends : sylma.ui.Base,

  onLoad : function() {

    var main = this;

    this.getForm().getNode('iframe').addEvent('load', function() {

      main.loadFrameContent(this);
    });
  },

  loadFrameContent : function(frame) {

    var body = frame.contentWindow.document.body;
    var content = document.all ? body.innerText : body.textContent;
    var result = null;

    if (content) {

      var response = JSON.parse(content);

      sylma.ui.parseMessages(response);
      this.sendComplete(response);

    }
  },

  getForm : function() {

    return this.getObject('uploader');
  },

  sendFile: function(fieldset, input) {

    this.fieldset = fieldset;
    this.input = input;
    
    var node = this.getForm().getNode();

    node.grab(input);
    node.submit();
  },

  sendComplete : function(response) {

    this.input.remove();
    this.fieldset.getObject('template').sendComplete(response);
  }

});
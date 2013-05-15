
sylma.ui.Form = new Class({

  Extends : sylma.ui.Base,

  initialize : function(options) {

    this.parent(options);

    this.getNode().addEvent('submit', this.submit.bind(this));
  },

  submit : function() {

    var node = this.getNode();

    var req = new Request.JSON({

      url : node.action,
      onSuccess: function(response) {

        var result = sylma.ui.parseMessages(response);
      }
    });
    //this.getNode().set('send', {url: 'contact.php', method: 'get'});

    req.post(node);

    return false;
  }
});


sylma.crud = {};

sylma.crud.Form = new Class({

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

sylma.crud.List = new Class({

  Extends : sylma.ui.Container,

  update : function(args) {

    if (this.get('send')) {

      args = Object.merge(this.get('send'), args);
    }

    return this.parent(args);
  }
})

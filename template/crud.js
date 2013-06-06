
sylma.crud = {};

sylma.crud.Form = new Class({

  Extends : sylma.ui.Base,
  mask : null,

  initialize : function(options) {

    this.parent(options);

    this.getNode().addEvent('submit', this.submit.bind(this));
    this.mask = new Element('div', {'class' : 'form-mask sylma-hidder'});
    this.getNode().grab(this.mask, 'top'); //, 'before'
  },

  showMask : function() {

    var size = this.getNode().getSize();

    this.mask.setStyle('width', size.x + 'px');
    this.mask.setStyle('height', size.y);
    this.mask.setStyle('display', 'block');
    this.mask.addClass('sylma-visible');
  },

  submit : function() {

    var node = this.getNode();
    var self = this;

    self.showMask();

    var req = new Request.JSON({

      url : node.action,
      onSuccess: function(response) {

        self.parseResult(response);
      }
    });
    //this.getNode().set('send', {url: 'contact.php', method: 'get'});

    req.post(node);

    return false;
  },

  parseResult : function(response) {

    if (response.messages) {

      for (var i in response.messages) {

        msg = response.messages[i];

        if (msg.arguments) {

          this.getObject(msg.arguments.alias).highlight();
          delete(response.messages[i]);
        }
      }
    }

    var redirect = response.content;

    sylma.ui.parseMessages(response, null, redirect);

    if (redirect) {

      window.history.back();
    }
    else {

      var self = this;
      this.mask.removeClass('sylma-visible');
      (function() { self.mask.setStyle('display', 'none'); }).delay(1000);
    }
  }

});

sylma.crud.Field = new Class({

  Extends : sylma.ui.Base,

  highlight : function() {

    this.getNode().addClass('field-statut-invalid');
  },

  downlight : function() {

    this.getNode().removeClass('field-statut-invalid');
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
});

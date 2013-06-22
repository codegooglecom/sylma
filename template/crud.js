
sylma.crud = {};

sylma.crud.Form = new Class({

  Extends : sylma.ui.Container,
  mask : null,

  options : {
    mask : true,
    method : 'post',
  },

  initialize : function(options) {

    this.parent(options);

    this.getNode().addEvent('submit', this.submit.bind(this));

    if (this.get('mask') === true) {

      this.mask = new Element('div', {'class' : 'form-mask sylma-hidder'});
      this.getNode().grab(this.mask, 'top'); //, 'before'
    }
  },

  showMask : function() {

    var size = this.getNode().getSize();

    this.mask.setStyle('width', size.x + 'px');
    this.mask.setStyle('height', size.y);
    this.mask.setStyle('display', 'block');
    this.mask.addClass('sylma-visible');
  },

  submit : function(args) {

    var node = this.getNode();
    var self = this;

    if (args)

    if (this.get('mask') === true) self.showMask();

    var req = new Request.JSON({

      url : node.action,
      onSuccess: function(response) {

        self.submitParse(response);
      }
    });
    //this.getNode().set('send', {url: 'contact.php', method: 'get'});

    var datas = this.loadDatas(args);

    if (this.get('method') === 'get') req.get(datas);
    else req.post(datas);

    return false;
  },

  loadDatas : function (args) {

    var node = this.getNode();

    return args ? node.toQueryString() + '&' + Object.toQueryString(args) : node.toQueryString();
  },

  submitParse : function(response) {

    if (response.messages) {

      for (var i in response.messages) {

        msg = response.messages[i];

        if (msg.arguments) {

          this.getObject(msg.arguments.alias).highlight();
          delete(response.messages[i]);
        }
      }
    }

    this.submitReturn(response);
  },

  submitReturn : function(response) {

    var redirect = response.content;

    var result = sylma.ui.parseMessages(response, null, redirect);

    if (!result.errors && redirect) {

      window.location = document.referrer;
      //window.history.back();
    }
    else {

      var self = this;
      this.mask.removeClass('sylma-visible');

      (function() {

        self.mask.setStyle('display', 'none');

      }).delay(1000);
    }
  }

});

sylma.crud.Field = new Class({

  Extends : sylma.ui.Base,

  setValue : function(val) {

    this.getInput().set('value', val);
  },

  getInput : function() {

    return this.getNode('input', false) || this.getNode();
  },

  highlight : function() {

    this.getNode().addClass('field-statut-invalid');
  },

  downlight : function() {

    this.getNode().removeClass('field-statut-invalid');
  }
});

sylma.crud.Text = new Class({

  Extends : sylma.crud.Field,

  setValue : function(val) {

    this.getInput().set('text', val);
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

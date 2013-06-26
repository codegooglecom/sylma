
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
  },

  prepareMask : function() {

    this.mask = new Element('div', {'class' : 'form-mask sylma-hidder'});
    this.getNode().grab(this.mask, 'top'); //, 'before'
  },

  showMask : function() {

    //var size = this.getNode().getSize();

    //this.mask.setStyle('width', size.x + 'px');
    //this.mask.setStyle('height', size.y);
    //this.mask.setStyle('display', 'block');
    //this.mask.addClass('sylma-visible');

    this.updateMask(true);
  },

  hideMask : function() {

    this.updateMask();
  },

  updateMask : function(val) {

    if (this.get('mask') === true) {

      if (val) val = 'disabled';

      this.getNode().getElements('input, select, textarea').each(function(el) {

        el.set('disabled', val);
      });
    }
  },

  submit : function(args) {

    var node = this.getNode();
    var self = this;

    var req = new Request.JSON({

      url : node.action,
      onSuccess: function(response) {

        self.submitParse(response);
      }
    });

    var datas = this.loadDatas(args);

    if (this.get('method') === 'get') req.get(datas);
    else req.post(datas);

    this.showMask();

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

      this.hideMask();
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

  initialize : function(props) {

    this.parent(props);

    var input = this.getNode('input');

    if (input.get('text').match(/^\s*$/)) {
      input.set('text');
    }
  },

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

sylma.crud.Row = new Class({

  Extends : sylma.ui.Base,

  onClick : function(e) {

    if (['A', 'BUTTON'].indexOf(e.target.tagName) > -1) {

      return true;
    }

    this.show();
  },

  show : function() {

    window.location = this.get('url');
  }
});

sylma.crud.Table = new Class({

  Extends : sylma.ui.Container,

  initialize : function(props) {

    this.parent(props);
    this.getObject('head').tmp.each(function(head) {
      head.updateOrder();
    });
  }
});

sylma.crud.Head = new Class({

  Extends : sylma.ui.Base,

  options : {
    dir : false,
    current : false
  },

  updateOrder : function() {

    var order = this.extractOrder();

    if (order && order.name === this.get('name')) {

      this.updateDir(order.dir);
      this.highlight();
    }
  },

  extractOrder : function() {

    var result = {};
    var name = this.getContainer().get('send').order;

    if (name) {

      if (name[0] === '!') {

        result.name = name.substr(1);
        result.dir = 1;
      }
      else {

        result.name = name;
      }
    }

    return result;
  },

  highlight : function() {

    this.parent();
    this.set('current', true);
  },

  downlight : function() {

    this.parent();
    this.set('current', false);
  },

  getContainer : function() {

    return this.getParent(1).getObject('container');
  },

  updateDir : function(dir) {

    dir = dir || false;

    this.set('dir', dir);
    this.getNode().toggleClass('order-desc', dir).blur();
  },

  update : function() {

    var current = this.get('current');

    this.getParent().tmp.each(function(head) {
      head.downlight();
    });

    var container = this.getContainer();

    if (current) this.updateDir(!this.get('dir'));
    else this.updateDir(false);

    container.update({order : (this.get('dir') ? '!' : '') + this.get('name')});

    this.highlight();

    return false;
  }
});

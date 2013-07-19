
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

  submit : function(e, args) {

    var node = this.getNode();
    var self = this;

    try {

      var datas = this.loadDatas(args);
      args = Object.merge(this.loadValues(), args);

      var req = new Request.JSON({

        url : node.action,
        onSuccess: function(response) {

          self.submitParse(response, args);
        }
      });

      if (this.get('method') === 'get') req.get(datas);
      else req.post(datas);
    }
    catch (e) {

      console.log(e.message);
      return false;
    }

    this.showMask();

    return false;
  },

  loadDatas : function (args) {

    var node = this.getNode();

    return args ? node.toQueryString() + '&' + Object.toQueryString(args) : node.toQueryString();
  },

  loadValues : function() {

    var result = {};

    this.getNode().getElements('input, select, textarea').each(function(el){
      var type = el.type;
      if (!el.name || el.disabled || type == 'submit' || type == 'reset' || type == 'file' || type == 'image') return;

      var value = (el.get('tag') == 'select') ? el.getSelected().map(function(opt){
          // IE
          return document.id(opt).get('value');
      }) : ((type == 'radio' || type == 'checkbox') && !el.checked) ? null : el.get('value');

      Array.from(value).each(function(val){
          if (typeof val != 'undefined') result[el.name] = val;
      });
    });

    return result;
  },

  submitParse : function(response, args) {

    this.parseMessages(response);
    this.submitReturn(response, args);
  },

  parseMessages : function(response) {

    if (response.messages) {

      for (var i in response.messages) {

        msg = response.messages[i];

        if (msg.arguments) {

          this.parseMessage(msg);
          delete(response.messages[i]);
        }
      }
    }

  },

  parseMessage : function(msg) {

    this.getObject(msg.arguments.alias).highlight();
  },

  submitReturn : function(response, args) {

    var redirect = response.content;

    var result = sylma.ui.parseMessages(response, null, redirect);

    if (!result.errors && redirect) {

      window.location.href = document.referrer;
    }
    else {

      this.hideMask();
    }
  }

});

sylma.crud.Field = new Class({

  Extends : sylma.ui.Base,

  initialize : function(props) {

    this.parent(props);
    this.props = props;
  },

  setValue : function(val) {

    this.getInput().set('value', val);
  },

  getInput : function() {

    return this.getNode('input', false) || this.getNode().getElement('input, select, textarea');
  },

  highlight : function() {

    this.getNode().addClass('field-statut-invalid');
  },

  downlight : function() {

    this.getNode().removeClass('field-statut-invalid');
  },

  clonePrepare : function() {

    var id = 'sylma' + Math.floor(Math.random(new Date().getSeconds()) * 999);
    this.getNode().addClass(id);

    var input = this.getInput();
    input.set('data-name', input.get('name'));
    input.set('name');

    this.cloneID = id;
  },

  clone : function(parent, node, position) {

    var props = this.props;

    props.node = node.getElements('.' + this.cloneID).pick();
    props.id = null;
    props.parentObject = parent;

    var result = sylma.ui.createObject(props);
    result.updateID(position);

    return result;
  },

  updateID : function(id) {

    var input = this.getInput();
    var name = input.get('data-name').replace(/\[\]/, '[' + id + ']');
    input.set('name', name);
    input.set('id', name);
    this.getNode().getElement('label').set('for', name);
  },
});

sylma.crud.Text = new Class({

  Extends : sylma.crud.Field,

  initialize : function(props) {

    this.parent(props);
    var input = this.getNode('input');

    if (input.get('value') && input.get('value').match(/^\s*$/)) {
      input.set('value');
    }
  },

  setValue : function(val) {

    this.getInput().set('value', val);
    //this.getInput().refresh();
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

sylma.crud.fieldset = {};

(function() {

  var self = this;

  this.Container = new Class({

    Extends : sylma.ui.Container,

    getCount : function() {

      return this.getNode('content').getChildren().length;
    },

    addTemplate : function() {

      var row = this.createTemplate(this.getCount() + 1);
      this.getNode('content').grab(row.getNode());
      setTimeout(function() {row.show()}, 1);
    },

    createTemplate : function(position) {

      return this.getObject('template').clone(position);
    }
  });

  this.Row = new Class({

    Extends : sylma.ui.Container,
    position : 0,

    setPosition : function(pos) {

      this.position = pos;
    },

    cloneContent : function(objects, tmp) {

      this.getNode().setStyle('display', 'block');

      var result = {
        objects : {},
        tmp : []
      }

      for (var i in objects) {

        result.objects[i] = this.cloneSub(objects[i]);
      }

      for (i = 0; i < tmp.length; i++) {

        result.tmp[i] = this.cloneSub(tmp[i]);
      }

      this.objects = result.objects;
      this.tmp = result.tmp;
    },

    cloneSub : function(obj) {

      var result = obj.clone(this, this.getNode(), this.position);

      return result;
    }
  });

  this.Template = new Class({

    Extends : this.Row,

    initialize : function(props) {

      this.parent(props);

      this.props = props;
      this.prepare();
    },

    prepare : function() {

      var objects = this.tmp.slice(0);
      Object.each(this.objects, function(item) {
        objects.push(item);
      });

      for (var i = 0; i < objects.length; i++) {

        objects[i].clonePrepare();
      }
    },

    clone : function(position) {

      var props = this.props;
      props.objects = {};

      props.node = this.getNode().clone(true);

      var clone = sylma.ui.createObject(props);

      clone.setPosition(position);
      clone.cloneContent(this.objects, this.tmp);

      return clone;
    }
  });

}).call(sylma.crud.fieldset);

sylma.crud.Group = new Class({

  Extends : sylma.ui.Container,
  elements : [],

  initialize : function(props) {

    this.parent(props);
    this.elements = Object.keys(this.objects);
  },

  highlight : function(alias) {

    var result = false;
    var obj = this.getObject(alias, false);

    if (obj) {

      result = true;
      obj.highlight();
    }

    return result;
  }

})
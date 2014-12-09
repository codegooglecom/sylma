sylma.crud = sylma.crud || {} ;

sylma.crud.Form = new Class({

  Extends : sylma.ui.Container,

  mask : null,

  options : {
    mask : true
  },

  errors : false,

  onLoad : function() {

    this.getNode().addEvent('submit', this.submit.bind(this));
    this.hideMask();
  },

  prepareMask : function() {

    this.mask = new Element('div', {'class' : 'form-mask sylma-hidder'});
    this.getNode().grab(this.mask, 'top'); //, 'before'
  },

  getID : function() {

    return this.getObject('id').getValue();
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
      else val = undefined;

      this.getInputs().each(function(el) {

        el.set('disabled', val);
      });
    }
  },

  getInputs : function() {

    return this.getNode().getElements('input, select, textarea');
  },

  clearInputs : function(inputs) {

    inputs = inputs || this.getInputs().filter(function(item) {
      return item.get('tag') !== 'input' || item.get('type') !== 'button';
    });

    inputs.each(function(item) {

      switch(item.get('tag')) {

        case 'input' :

          switch(item.getAttribute('type')) {

            case 'checkbox' :
            case 'radio' : item.set('checked'); break;

            default :

              item.set('value');
          }

          break;

      case 'textarea' :

        item.set('value');
        break;

      case 'select' :

        item.getChildren().set('selected');
        break;
      }
    });
  },

  submit : function(e, args, callback) {

    return this.submitSend(e, args, callback);
  },

  submitSend : function(e, args, callback) {

    var node = this.getNode();

    this.fireEvent('submit');

    callback = callback || function(response) {

      this.submitParse(response, args);

    }.bind(this);

    try {

      var datas = this.loadDatas(args);
      args = Object.merge(this.loadValues(), args);

      var req = new Request.JSON({

        url : node.action,
        onSuccess: callback
      });

      // @todo remove
      if (this.get('method') !== undefined) {

        console.log('method option do not work anymore, use method attribute instead')
      }

      var method = this.getNode().getAttribute('method');

      if (method && method.toLowerCase() === 'post') {

        req.post(datas);
      }
      else {

        req.get(datas);
      }
    }
    catch (error) {

      console.log(error.message);
      return false;
    }

    this.showMask();

    if (e) e.preventDefault();
    return false;
  },

  submitDelay : function(e, args) {

    this.updateDelay(function() {

      this.submit(e, args, function(response, args) {

        this.updater.running = false;

        if (this.updater.obsolete) {

          this.updater.obsolete = false;
          this.submitDelay(e, args);
        }
        else {

          this.submitParse(response, args);
        }

      }.bind(this));

    }.bind(this), 200);
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
    this.fireEvent('complete', [response, args]);
  },

  parseMessages : function(response) {

    this.errors = false;

    if (response.messages) {

      var messages = Object.filter(response.messages, function(msg) {

        if (msg.arguments) {

          if (msg.arguments.error) {

            sylma.ui.showMessage(msg.content);
            this.errors = true;
          }
          else {

            this.parseMessage(msg);
          }
        }

        return !msg.arguments;

      }.bind(this));

      response.messages = messages;
    }
  },

  parseMessage : function(msg) {

    var alias = msg.arguments.alias;
    var path = this.parseMessageAlias(alias);

    if (path.sub) {

      alias = path.alias;
    }

    var field = this.getObject(alias);

    if (field) {

      field.highlight(path.sub);
    }
    else {

      console.log("Field '" + alias + "' is missing");
    }

    this.errors = true;

    sylma.ui.showMessage(msg.content);
  },

  parseMessageAlias: function(alias) {

    var sub;

    if (alias.indexOf('[') !== -1) {

      var match = alias.match(/(.+)\[(\d+)\](.+)/);

      sub = {
        alias : match[3],
        key : match[2]
      };

      alias = match[1];
    }

    return {
      alias : alias,
      sub : sub
    };
  },

  submitReturn : function(response, args) {

    var redirect = !this.get('ajax');
    var error = this.errors || response.error;
    //var content = response.content;

    var result = sylma.ui.parseMessages(response, null, redirect && !error);

    if (result.errors || error) {

      this.hideMask();
    }
    else if (redirect) {

      this.redirect();
    }
    else {

      this.submitSuccess();
    }
  },

  redirect : function() {

    window.location.href = document.referrer;
  },

  submitSuccess : function() {

    this.fireEvent('success');
    //throw new Error('Must do something');
  },

  deleteItem : function() {

    this.show(this.getNode('delete'));
  },

  deleteConfirm : function(callback) {

    this.deleteSend(callback);
  },

  deleteSend: function(callback) {

    var id = this.getNode().getElements('input[@name=id]').pick().get('value');

    this.send(this.get('delete'), {id : id}, function(response) {

      if (!response.error) {

        this.deleteSuccess();
        callback && callback();
      }

    }.bind(this), false, !this.get('ajax'));
  },

  deleteCancel : function() {

    this.hide(this.getNode('delete'));
  },

  deleteSuccess : function() {

    if (!this.get('ajax')) {

      this.redirect();
    }
  },

  cancel : function() {

    window.history.go(-1);
  }
});

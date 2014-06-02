sylma.crud = sylma.crud || {} ;

sylma.crud.Form = new Class({

  Extends : sylma.ui.Container,
  Implements : Events,

  mask : null,

  options : {
    mask : true
  },

  onLoad : function() {

    this.getNode().addEvent('submit', this.submit.bind(this));
    this.updateMask(false);
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
      else val = undefined;

      this.getInputs().each(function(el) {

        el.set('disabled', val);
      });
    }
  },

  getInputs : function() {

    return this.getNode().getElements('input[type=^button], select, textarea');
  },

  clearInputs : function(inputs) {

    inputs = inputs || this.getInputs();

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

    var node = this.getNode();

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
    this.fireEvent('complete');
  },

  parseMessages : function(response) {

    var msg;

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

    var alias = msg.arguments.alias;
    var path = this.parseMessageAlias(alias);

    if (path.sub) {

      this.getObject(path.alias).highlight(path.sub);
    }
    else {

      this.getObject(alias, true).highlight();
    }

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
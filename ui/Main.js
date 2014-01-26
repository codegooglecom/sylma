/**
 *
 */

var sylma = {};

sylma.debug = {
  log : false
}

sylma.modules = {};
sylma.factory = {
  debug : false
};

sylma.binder = {
  classes : {},
  objects : {}
};

sylma.classes = {

  ui : new Class({

    roots : [],
    windowLoaded : false,
    uID : 1,

    cookie : {

      name : 'sylma-main'
    },

    tmp : {},

    load : function(parent, objects) {

      this.loadMessages();
      this.loadObjects(parent, objects);
    },

    loadObjects : function(parent, objects) {

      if (parent && objects) {

        var length = Object.getLength(objects);

        if (length > 1) {

          this.loadMultiple(objects, parent);
        }
        else {

          this.loadOne(objects, parent);
        }
      }
    },

    loadPath : function(path) {

      var result = window;
      var lastPath = 'window';

      path.split('.').each(function(item) {

        result = result[item];

        if (!result) {

          throw 'No property named "' + item + '" in ' + lastPath;
        }

        lastPath += '.' + item;
      });

      return result;
    },

    loadMultiple : function(objects, parent) {

      var result;

      for (var obj in objects) {

        result = this.createObject(objects[obj]);
        parent[obj] = result;

        this.loadResult(result);
      }
    },

    extractFirst : function(object) {

      for (var result in object) return result;
    },

    loadOne : function(objects, parent) {

      var first = this.extractFirst(objects);
      var result = this.createObject(objects[first]);

      parent[first] = result;
      this.loadResult(result);
    },

    loadResult : function(result) {

      if (this.windowLoaded) {

        this.loadArray([result]);
      }
      else {

        this.roots.push(result);
      }
    },

    createObject : function(props) {

      if (!props.extend) {

        sylma.log(props);
        throw new Error('No path defined');
      }

      var parent = this.loadPath(props.extend);

      return new parent(props);
    },

    onWindowLoad : function() {

      this.windowLoaded = true;
      this.loadArray(this.roots);
    },

    loadArray : function(objs) {

      var obj, len = objs.length;

      for (var i = 0; i < len; i++) {

        obj = objs[i];

        if (!obj.windowLoaded) {

          obj.windowLoaded = true;

          if (obj.onWindowLoad) obj.onWindowLoad();
          if (obj.onLoad) obj.onLoad();
        }
      }
    },

    importNode : function(val, name, root) {

      name = name || 'div';

      var el = new Element(name, {
        html : val
      });

      return root ? el : el.getChildren();
    },

    objectToString : function(val) {

      return JSON.stringify(val);
    },

    stringToObject : function(val) {

      return JSON.parse(val);
    },
/*
    arrayToObject : function(val) {

      var result = {};

      val.each(function(item, key) {
        result[key] = item;
      });

      return result;
    },
*/
    loadMessages : function() {

      var val = Cookie.read(this.cookie.name);

      if (val) {

        var result = this.stringToObject(val);
        this.parseMessages(result);
        Cookie.dispose(this.cookie.name);
      }
    },

    addMessage : function(content, container) {

      container = container || $('messages');
      container.adopt(content);
    },

    parseMessages : function(result, container, delay) {

      if (result.errors && delay) {

        sylma.log('Cannot redirect while exception occured');
      }

      if (!result.errors && delay) {

        this.cookie.handler = Cookie.write(this.cookie.name, this.objectToString(result));
      }
      else {

        if (result.messages) {

          var msg;

          if (!$('sylma-messages')) {

            $(document.body).grab(new Element('div', {id : 'sylma-messages'}), 'top');
          }

          var el;

          for (var i in result.messages) {

            this.showMessage(result.messages[i].content);
            //el.addClass('sylma-visible');
          }
        }

        if (result.errors) {

          for (var i in result.errors) {

            this.addMessage(this.importNode(result.errors[i].content), container);
          }
        }
      }

      return result;
    },

    showMessage : function(msg) {

      var el = new Element('div', {html : msg, 'class' : 'sylma-message sylma-hidder'});
      var id = 'sylma-messages';
      var container = $(id);

      if (!container) {

        container = new Element('div', {
          id : id
        });

        window.document.body.grab(container, 'top');
      }

      this.addMessage(el, container);
      window.getComputedStyle(el).opacity;
      el.addClass('sylma-visible');

      (function() {

        el.removeClass('sylma-visible');

        (function() {

          el.destroy();

        }).delay(2000);

      }).delay(5000);
    },

    /**
     * @param {bool} [get=FALSE] - Send request in GET, POST by default
     */
    send : function(path, args, callback, get) {

      args = args || {};
      //var self = this;

      var req = new Request.JSON({

        url : path + '.json',
        onSuccess: function(response) {

          sylma.ui.parseMessages(response);
          if (callback) callback(response);
        }
      });

      return get ? req.get(args) : req.post(args);
    },

    isTouched : function() {

      return 'ontouchstart' in document.documentElement;
    },

    getHash : function() {

      var hash = window.location.hash;
      return hash.indexOf('#') == 0 ? hash.substr(1) : hash;
    },

    generateID : function(prefix) {

      return prefix + this.uID++;
    },

    getVendorPrefix : function(style) {

      var result;

      switch (Browser.name) {

        case 'firefox' : result = 'Moz'; break;
        case 'safari' :
        case 'chrome' : result = 'webkit'; break;
        case 'opera' : result = 'O'; break;
        default : result = style; break;
      }

      return result + style.capitalize();
    },

    addEventTransition : function(el, callback, property) {

      property = property || 'opacity';
      var name = Browser.firefox ? 'transitionend' : this.getVendorPrefix('transition') + 'End';

      var handler = function(e) {
        
        if (e.propertyName === property) {

          this.removeEventListener(name, handler);
          callback && callback(e);
        }
      };

      el.addEventListener(name, handler);
    }
  })
};

sylma.ui = new sylma.classes.ui;
sylma.log = function(msg) {

  if (sylma.debug.log) {

    console.log(msg);
  }
}

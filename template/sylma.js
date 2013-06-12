/* Document JS */

var sylma = {};

sylma.modules = {};

sylma.binder = {
  classes : {},
  objects : {}
};

sylma.classes = {

  ui : new Class({

    cookie : {

      name : 'sylma-main'
    },

    tmp : {},

    load : function(parent, objects) {

      this.loadMessages();

      if (parent && objects) {

        var length = objects.length;

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

      for (var obj in objects) {

        parent[obj] = this.createObject(objects[obj]);
      }
    },

    extractFirst : function(object) {

      for (var result in object) return result;
    },

    loadOne : function(objects, parent) {

      var first = this.extractFirst(objects);
      parent[first] = this.createObject(objects[first]);
    },

    createObject : function(props) {

      if (!props.extend) {

        console.log(props);
        throw 'No path defined';
      }

      var parent = this.loadPath(props.extend);

      return new parent(props);
    },

    import : function(val, name) {

      name = name || 'div';

      var el = new Element(name, {
        html : val
      });

      return el.getChildren();
    },

    objectToString : function(val) {

      return JSON.stringify(val);
    },

    stringToObject : function(val) {

      return JSON.parse(val);
    },

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

    toggleVisibility : function() {


    },

    parseMessages : function(result, container, delay) {

      if (result.errors && delay) {

        console.log('Cannot redirect while exception occured');
      }

      if (delay) {

        this.cookie.handler = Cookie.write(this.cookie.name, this.objectToString(result));
      }
      else {

        if (result.messages) {

          var msg;

          if (!$('sylma-messages')) {

            $(document.body).grab(new Element('div', {id : 'sylma-messages'}), 'top');
          }

          for (var i in result.messages) {

            msg = result.messages[i];
            var el = new Element('div', {html : msg.content, 'class' : 'sylma-message sylma-hidder'});
            this.addMessage(el, $('sylma-messages'));
            window.getComputedStyle(el).opacity;
            el.addClass('sylma-visible');
            (function() { el.removeClass('sylma-visible'); (function() { el.destroy(); }).delay(2000) }).delay(5000);
            //el.addClass('sylma-visible');
          }
        }

        if (result.errors) {

          for (var i in result.errors) {

            this.addMessage(this.import(result.errors[i].content), container);
          }
        }
      }

      return result;
    }
  })
}

sylma.ui = new sylma.classes.ui;

(function() {

  var ui = this;

  this.Base = new Class({

    Implements : Options,

    /**
     * List of unnamed sub-objects
     */
    tmp : [],
    node : null,
    nodes : [],
    options : {

    },
    objects : {},

    get : function(key) {

      if (!this.options[key]) {

        throw 'No option named ' + key;
      }

      return this.options[key];
    },

    initialize : function(props) {

      props = this.loadProperties(props);

      this.initBasic(props);

      if (props.options) this.initOptions(props.options);
      if (props.objects) this.initObjects(props.objects);
      if (props.events) this.initEvents(props.events);
      if (props.nodes) this.initNodes(props.nodes);
    },

    loadProperties : function(props) {

      var binder = sylma.binder.classes[props.binder];

      if (!binder) {

        throw 'No valid binder defined with : ' + props.binder;
      }

      return Object.merge(props, binder);
    },

    initBasic : function(options) {

      if (!options.id) throw 'No node associated';

      this.node = $(options.id);
      this.parentObject = options.parentObject;
      this.prepareNodes(this.node);
    },

    initObjects : function(objects) {

      for (var key in objects) {

        this.initObject(key, objects[key])
      }
    },

    initObject : function(key, options) {

      options.parentObject = this;
      var obj = ui.createObject(options);

      if (options.name) this.objects[key] = obj;
      else this.tmp.push(obj);
    },

    initNodes : function(nodes) {

      for (var key in nodes) {

        this.nodes[key] = this.getNode().getElement('.' + nodes[key]);
      }
    },

    initEvents : function(events) {

      for (var name in events) {

        this.initEvent(events[name]);
      }
    },

    initEvent : function(event) {

      var name = event.name;
      var nodes;

      if (event.node) {

        nodes = this.getNode().getElements('.' + event.node);
        this.prepareNodes(nodes);
      }
      else {

        nodes = this.getNode();
      }

      nodes.addEvent(name, event.callback);
    },

    initOptions : function(options) {

      //this.initPropertiesBasic(properties.basic);
      //delete(properties.basic);

      for (var option in options) {

        this.options[option] = options[option]
      }
    },

    prepareNodes : function(nodes) {

      nodes.store('sylma-object', this);
      nodes.store('sylma-parent', this.getParent());
    },

    /**
     * @return Element
     */
    getNode : function(name) {

      var result;

      if (name) {

        if (!this.nodes[name]) {

          throw 'Unknow node ' + name;
        }

        result = this.nodes[name];
      }
      else {

        result = this.node;
      }

      return result;
    },

    getParent : function() {

      return this.parentObject;
    },

    getObject : function(name) {

      if (!this.objects[name]) {

        throw 'No object named ' + name;
      }

      return this.objects[name];
    },

    call : function(path, args) {

      args = args || {};
      //var self = this;

      var req = new Request.JSON({

        url : path + '.json',
        onSuccess: function(response) {

          sylma.ui.parseMessages(response);
        }
      });

      req.post(args);
    }
  });

  this.Container = new Class({

    Extends : this.Base,

    update : function(args, path) {

      var self = this;
      var path = path || this.get('path');

      var req = new Request.JSON({

        url : path + '.json',
        onSuccess: function(response) {

          var result = sylma.ui.parseMessages(response);
          var name = self.getNode().getParent().tagName || 'div';

          sylma.ui.import(result.content, name).replaces(self.getNode());

          //console.log(result.objects[sylma.ui.extractFirst(result.objects)]);

          if (result.classes) {

            eval(result.classes);
            Object.merge(sylma.binder.classes, classes);
          }

          var props = result.objects[sylma.ui.extractFirst(result.objects)];
          self.initialize(props);
        }
      });

      req.get(args);
    }
  })


}).call(sylma.ui);

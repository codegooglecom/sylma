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
        throw new Error('No path defined');
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

            msg = result.messages[i];
            el = new Element('div', {html : msg.content, 'class' : 'sylma-message sylma-hidder'});

            this.addMessage(el, $('sylma-messages'));
            window.getComputedStyle(el).opacity;
            el.addClass('sylma-visible');

            (function() {

              this.removeClass('sylma-visible');

              (function() {

                this.destroy();

              }).bind(this).delay(2000);

            }).bind(el).delay(5000);
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
    settings : {},
    options : [],

    props : {

    },

    objects : {},

    get : function(key, debug) {

      if (!this.options[key] && debug) {

        throw 'No option named ' + key;
      }

      return this.options[key];
    },

    set : function (key, val) {

      this.options[key] = val;
    },

    initialize : function(props) {

      this.tmp = [];

      this.initBasic(props);

      //if (props.methods) this.initMethods(props.methods);
      if (this.events) this.initEvents(this.events);
      if (this.nodes) this.initNodes(this.nodes);

      if (props.options) this.initOptions(props.options);
      if (props.objects) this.initObjects(props.objects);
    },

    initBasic : function(props) {

      if (!props.id) throw 'No node associated';

      this.node = $(props.id);

      if (!this.node) {

        throw new Error('Main node [@id=' + props.id + '] not found');
      }

      this.parentObject = props.parentObject;
      this.parentKey = props.parentKey;

      this.prepareNodes(this.node);
    },

    initObjects : function(objects) {

      for (var key in objects) {

        this.initObject(key, objects[key]);
      }
    },

    initObject : function(key, props) {

      props.parentObject = this;
      props.parentKey = props.name ? key : this.tmp.length;

      var obj = ui.createObject(props);

      if (props.name) this.objects[key] = obj;
      else this.tmp.push(obj);
    },

    initNodes : function(nodes) {

      for (var key in nodes) {

        this.nodes[key] = this.getNode().getElement('.' + nodes[key]);
      }
    },

    initMethods : function(methods) {

      for (var name in methods) {

        this.initMethod(name, methods[name]);
      }
    },

    initMethod : function(name, method) {

      this[name] = method.bind(this);
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

        if (!nodes) {

          throw new Error('No node [.' + event.node + '] found to bind event on ' + event.name);
        }

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

      if (!nodes) {

        throw new Error('No nodes sent');
      }

      nodes.store('sylma-object', this);
      nodes.store('sylma-parent', this.getParent());
    },

    /**
     * @return Element
     */
    getNode : function(name, debug) {

      var result;

      if (name) {

        if (!this.nodes[name]) {

          if (debug) throw new Error('Unknow node ' + name);
        }

        result = this.nodes[name];
      }
      else {

        result = this.node;
      }

      return result;
    },

    getParent : function(depth) {

      var result;

      if (depth && this.parentObject) result = this.parentObject.getParent(--depth);
      else result = this.parentObject;

      return result;
    },

    getObject : function(name) {

      if (!this.objects[name]) {

        throw new Error('No object named ' + name);
      }

      return this.objects[name];
    },

    send : function(path, args) {

      args = args || {};
      //var self = this;

      var req = new Request.JSON({

        url : path + '.json',
        onSuccess: function(response) {

          sylma.ui.parseMessages(response);
        }
      });

      req.post(args);
    },

    toggleLight : function() {

      this.getNode().toggleClass('sylma-highlight');
    },

    highlight : function() {

      this.getNode().addClass('sylma-highlight');
    },

    downlight : function() {

      this.getNode().removeClass('sylma-highlight')
    }
  });

  this.Container = new Class({

    Extends : this.Base,

    update : function(args, path, inside) {

      this.set('sylma-inside', inside);

      var self = this;
      var path = path || this.get('path');

      var req = new Request.JSON({

        url : path + '.json',
        onSuccess : self.updateSuccess.bind(self)
      });

      req.get(args);
    },

    updateSuccess : function(response) {

      var result = sylma.ui.parseMessages(response);
      var name = this.getNode().getParent().tagName || 'div';
      var target;

      if (this.get('sylma-inside', false)) {

        target = this.getNode().getFirst();
      }
      else {

        target = this.getNode();
      }

      sylma.ui.import(result.content, name).replaces(target);

      //console.log(result.objects[sylma.ui.extractFirst(result.objects)]);

      if (result.classes) {

        eval(result.classes);
        Object.merge(sylma.binder.classes, classes);
      }

      var props = result.objects[sylma.ui.extractFirst(result.objects)];
      props.parentObject = this.getParent();

      this.initialize(props);
    },

    show : function() {

      this.getNode().addClass('sylma-visible');
    },

    hide : function() {

      this.getNode().removeClass('sylma-visible');
    }
  })


}).call(sylma.ui);

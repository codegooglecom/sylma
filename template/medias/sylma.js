/* Document JS */

var sylma = {};

sylma.modules = {};

sylma.binder = {
  classes : {},
  objects : {}
};

sylma.classes = {

  ui : new Class({

    roots : [],
    windowLoaded : false,

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

        if (result.onLoad) result.onLoad();
      }
      else {

        this.roots.push(result);
      }
    },

    createObject : function(props) {

      if (!props.extend) {

        console.log(props);
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

        if (obj.onWindowLoad) obj.onWindowLoad();
        if (obj.onLoad) obj.onLoad();
      }
    },

    importNode : function(val, name) {

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

            this.addMessage(this.importNode(result.errors[i].content), container);
          }
        }
      }

      return result;
    },

    send : function(path, args, get, callback) {

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
    }
  })
}

sylma.ui = new sylma.classes.ui;

(function() {

  var ui = self = this;

  this.Base = new Class({

    Implements : Options,

    sylma : {},

    /**
     * List of unnamed sub-objects
     */
    tmp : [],
    node : null,
    nodes : [],
    settings : {},
    options : [],

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

      //if (props.sylma) this.sylma = Object.merge(this.sylma, props.sylma);

      this.initBasic(props);

      //if (props.methods) this.initMethods(props.methods);
      if (this.events) this.initEvents(this.events);
      if (this.nodes) this.initNodes(this.nodes);

      if (props.options) this.initOptions(props.options);

      this.initParents(props.sylma);
      this.initParentName(this.sylma);

      if (props.objects) this.initObjects(props.objects);
    },

    initBasic : function(props) {

      this.parentObject = props.parentObject;
      this.parentKey = props.parentKey;

      if (!props.id && !props.node) {

        throw new Error('No node associated');
      }

      this.node = props.node ? $(props.node) : $(props.id);

      if (!this.node) {

        throw new Error('Main node [@id=' + props.id + '] not found');
      }

      this.prepareNodes(this.node);
    },

    initParents : function(props) {

      this.sylma.parents = props && props.parents ? props.parents : {};
    },

    initParentName : function(props) {

      var name = props.parentName;

      if (name) {

        this.sylma.parents[name] = this;
        //console.log(this.getParents());
      }
    },

    initObjects : function(objects) {

      for (var key in objects) {

        this.initObject(key, objects[key]);
      }
    },

    initObject : function(key, props) {

      props.parentObject = this;
      props.parentKey = props.name ? key : this.tmp.length;
      props.sylma = {
        parents : this.getParents()
      };

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

    onWindowLoad : function() {

      sylma.ui.loadArray(this.tmp);
      sylma.ui.loadArray(Object.values(this.objects));
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

    getParent : function(key, debug) {

      var result;
/*
      if (!this.parentObject && debug === null) {

        throw new Error('No parent defined');
      }
*/
      if (key && this.parentObject) {

        if (typeOf(key) === 'string') {

          result = this.getParentFromName(key);
        }
        else {

          result = this.getParentFromDepth(key);
        }
      }
      else {

        result = this.parentObject;
      }

      return result;
    },

    getParentFromDepth : function(depth) {

      return this.parentObject.getParent(--depth);
    },

    getParentFromName : function(name) {

      return this.sylma.parents[name];
    },

    getParents : function() {

      return this.sylma.parents;
    },

    getObject : function(name, debug) {

      var result;

      if (debug === undefined) debug = true;

      if (!this.objects[name]) {

        if (debug) throw new Error('No object named ' + name);
        result = null;
      }
      else {

        result = this.objects[name];
      }

      return result;
    },

    send : function(path, args, get, callback) {

      return sylma.ui.send(path, args, get, callback);
    },

    toggleLight : function() {

      this.getNode().toggleClass('sylma-highlight');
    },

    highlight : function() {

      this.getNode().addClass('sylma-highlight');
    },

    downlight : function() {

      this.getNode().removeClass('sylma-highlight');
    },

    remove : function() {

      this.hide();
      this.getNode().morph({
        padding : 0,
        margin : 0,
        height : 0
      });

      (function() {

        this.getNode().destroy();

      }).delay(2000, this);
    },

    importResponse : function(response, parent, target) {

      if (response.classes) {

        eval(response.classes);
        Object.merge(sylma.binder.classes, classes);
      }

      if (target) {

        result = response;
      }
      else {

        var key = sylma.ui.extractFirst(response.objects);

        if (!key) {

          throw new Error('No root object found');
        }

        var result = response.objects[key];
        result.parentObject = parent;
        //result.parentKey = key;
      }

      return result;
    }
  });

  this.Container = new Class({

    Extends : this.Base,

    update : function(args, path, inside) {

      if (inside !== undefined) this.set('sylma-inside', inside);

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

      var node = sylma.ui.importNode(result.content, name);
      this.updateContent(result, node, target);
    },

    updateContent : function(result, node, target) {

      if (target) {

        node.replaces(target);

        var props = this.importResponse(result, this.getParent());
        this.initialize(props);
      }
      else {

        this.getNode().adopt(node);
        var props = this.importResponse(result, this.getParent(), true);
        if (props.objects) this.initObjects(props.objects);
      }
    },

    show : function() {

      this.getNode().addClass('sylma-visible');
    },

    hide : function() {

      this.getNode().removeClass('sylma-visible');
    }
  });


}).call(sylma.ui);

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

        this.loadArray([result]);
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

        if (!obj.windowLoaded) {

          obj.windowLoaded = true;

          if (obj.onWindowLoad) obj.onWindowLoad();
          if (obj.onLoad) obj.onLoad();
        }
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

      this.addMessage(el, $('sylma-messages'));
      window.getComputedStyle(el).opacity;
      el.addClass('sylma-visible');

      (function() {

        el.removeClass('sylma-visible');

        (function() {

          el.destroy();

        }).delay(2000);

      }).delay(5000);
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
    },

    generateID : function(prefix) {

      return prefix + this.uID++;
    }
  })
}

sylma.ui = new sylma.classes.ui;


sylma.ui.Base = new Class({

  Implements : Options,

  /**
   * List of unnamed sub-objects
   */
  tmp : [],
  node : null,
  nodes : {},
  settings : {},
  options : {},
  windowLoaded : false,
  sylma : {
    key : null,
    parents : {}
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
    this.initSylma(props);
    this.initNode(props);

    if (props.options) this.initOptions(props.options);

    this.initParentName(this.sylma);

    if (props.objects) this.initObjects(props.objects);

    if (this.onReady) this.onReady();
  },

  initBasic : function(props) {

    this.parentObject = props.parentObject;

    if (props.onReady) this.onReady = props.onReady;
    if (props.onLoad) this.onLoad = props.onLoad;
  },

  initNode : function(props) {

    if (!props.id && !props.node) {

      throw new Error('No node associated');
    }

    var node = props.node ? props.node : $(props.id);

    if (!node) {

      //console.log(props);
      throw new Error('Main node [@id=' + props.id + '] not found');
    }

    this.node = node;

    this.prepareNodes(this.node);

    this.initEvents(this.events);
    this.initNodes(this.nodes);
  },

  initSylma : function(props) {

    Object.append(this.sylma, props.sylma);
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

      this.initObject(objects[key], key, true);
    }
  },

  initObject : function(props, key) {

    var container = this.initObjectContainer(props, key);

    props.parentObject = this;
    props.sylma = Object.append({
      key : container.key,
      parents : Object.append({}, this.getParents()),
      splice : container.splice
    }, props.sylma);

    var obj = this.createObject(props);
    this.insertObject(obj, container);

    return obj;
  },

  initObjectContainer: function(props, key) {

    var result;

    if (props.name) {

      result = {
        parent : this.objects,
        key : key
      };
    }
    else {

      result = this.initObjectSplice(this.tmp, key);
    }

    return result;
  },

  initObjectSplice : function(container, key) {

    var length = container.length;

    var result = {
      parent : container,
      key : key === undefined ? length : key
    };

    if (key !== undefined && key !== length) {

      if (key > length) {

        throw new Error("Cannot set sub-obj key '" + key + "', key must follow length of container (" + length + ')');
      }

      result.splice = true;
    }

    return result;
  },

  insertObject: function(obj, container) {

    var key = container.key;

    if (container.splice) {

      container.parent.splice(key, 0, obj);
      container.parent.slice(key + 1).each(function(item, subkey) {

        item.setKey(key + subkey + 1);
      });
    }
    else {

      container.parent[key] = obj;
    }
  },

  setKey : function(key) {

    this.sylma.key = key;
  },

  getKey : function() {

    return this.sylma.key;
  },

  createObject : function(props) {

    return sylma.ui.createObject(props);
  },

  initNodes : function(nodes) {

    for (var key in nodes) {

      var node = this.getNode().getElement('.' + nodes[key]);

      if (!node) {

        throw new Error('Node ' + key + ' not found');
      }

      this.nodes[key] = node.length ? node[0] : node;
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

    var node = this.getNode();

    new Fx.Morph(node, {
      //duration : 'long',
      onComplete : function() {

        node.destroy();
        this.destroy();

      }.bind(this)
    }).start('.destroy');
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
  },

  destroy : function() {

    var parent = this.getParent();

    return parent && parent.destroyChild(this.sylma.key);
  },

  destroyChild : function(key) {

    if (key === undefined) {

      throw new Error('Undefined key for destroy');
    }

    if (typeof(key) === 'number') {

      this.tmp.splice(key, 1);

      this.tmp.slice(key).each(function(item, key) {

        item.setKey(key);
      });
    }
    else {

      delete this.objects[key];
    }
  }
});

sylma.ui.Container = new Class({

  Extends : sylma.ui.Base,
  /*
  sylma : {
    template : {
      alias : undefined,
      mixed : false,
      classes : {}
    }
  },
  */

  initSylma : function(props) {

    var template = this.sylma.template;
    this.parent(props);

    Object.append(this.sylma.template, template);
  },

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

  useTemplate : function() {

    return !!this.sylma.template;
  },

  isMixed : function() {

    return this.sylma.template && this.sylma.template.mixed;
  },

  initObjectContainer : function(props, key) {

    var result;

    if (!this.useTemplate()) {

      result = this.parent(props, key);
    }
    else {

      var tpl = props.sylma && props.sylma.template;
      var alias = tpl.alias;

      if (this.isMixed()) {

        result = this.parent(props, key);
      }
      else {

        if (!this.objects[alias]) {

          this.objects[alias] = [];
        }

        result = this.initObjectSplice(this.objects[alias], key);
      }
    }

    return result;
  },

  buildObject : function(alias, args, position) {

    var _class = this.sylma.template.classes[alias];

    if (!_class) {

      throw new Error('Cannot find class : ' + alias);
    }

    var props;
    var basic = {
      extend : _class.name,
      sylma : {
        template : {
          alias : alias
        }
      }
    };

    if (args) {

      props = args._init || {};
      delete args._init;

      props.options = args;

      props = Object.merge(basic, props);
    }
    else {

      props = basic;
    }

    var result = this.initObject(props, position);

    return result;
  },

  add : function(alias, args, key) {

    var target;
    var _class = this.sylma.template.classes[alias];
    var result = this.buildObject(alias, args, key);

    if (result.sylma.splice) {

      ++key;

      var next = this.isMixed() ? this.tmp[key] : this.getObject(alias)[key];
      target = next.getNode();
    }
    else {

      target = this.getNode().getElement('.' + _class.node);
    }

    if (!target) {

      throw new Error('Target node ".' + _class.node + '" not found for sub object');
    }

    result.addTo(target);

    return result;
  },

  onWindowLoad : function() {

    if (this.useTemplate()) {

      if (this.isMixed()) {

        sylma.ui.loadArray(this.tmp);
      }
      else {

        Object.each(this.objects, function(item) {

          sylma.ui.loadArray(item);
        });
      }
    }
    else {

      this.parent();
    }
  },

  show : function() {

    this.getNode().addClass('sylma-visible');
  },

  hide : function() {

    this.getNode().removeClass('sylma-visible');
  },

  destroyChild : function(key, alias) {

    var container;

    if (this.isMixed()) {

      this.parent(key);
    }
    else {

      var container = this.objects[alias];

      container.splice(key, 1);
      container.slice(key).each(function(item, key) {

        item.setKey(key);
      });
    }

  }
});

sylma.ui.Template = new Class({

  Extends : sylma.ui.Container,

  initTemplate : function() {

    var el = sylma.ui.importNode(this.compileTemplate())[0];
    this.node = el;

    return el;
  },

  getAlias : function() {

    return this.sylma.template.alias;
  },

  compileTemplate : function() {

    var values = this.options;

    for (var i in this.sylma.template.autoloaded) {

      var alias = this.sylma.template.autoloaded[i];
      if (!values[alias]) values[alias] = [{}];
    }

    return this.buildTemplate(values);
  },

  convertChildren : function(children) {

    return typeOf(children) === 'object' ? Object.values(children) : children;
  },

  buildObjects : function(alias, objects) {

    return this.buildObjectsAll(objects, alias)
  },

  buildObjectsAll : function(objects, alias) {

    var result = [];
    objects = objects || [];

    this.convertChildren(objects).each(function(item) {

      var obj = this.buildObject(alias || item._alias, item);
      result.push(obj.compileTemplate());

    }.bind(this));

    return result.join('');
  },

  initNode : function(props, deep) {

    if (!props) {

      this.parent({
        id : this.id
      });
    }
    else if (props.sylma && props.sylma.template) {

      this.id = sylma.ui.generateID('sylma');
    }
    else {

      this.parent(props);
    }

    if (deep) {

      if (this.isMixed()) {

        this.tmp.each(function(item) {

          item.initNode(null, deep);
        });
      }
      else {

        Object.each(this.objects, function(collection) {

          collection.each(function(item) {

            item.initNode(null, deep);
          });
        });
      }
    }
  },

  onLoad : function() {},

  addTo : function(node) {

    var el = this.initTemplate();

    el.inject(node, 'before');

    this.initNode({node : el}, true);

    sylma.ui.loadArray([this]);
  },

  destroy : function() {

    //this.parent();
    this.getParent().destroyChild(this.getKey(), this.getAlias());
  }

});


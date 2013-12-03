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

      //sylma.log(props);
      throw new Error('Main node [@id=' + props.id + '] not found');
    }

    this.node = node;

    this.prepareNodes(this.node);

    this.initEvents(this.sylma.events);
    this.initNodes(this.nodes);
  },

  initSylma : function(props) {

    Object.append(this.sylma, props.sylma);
  },

  initParentName : function(props) {

    var name = props.parentName;

    if (name) {

      this.sylma.parents[name] = this;
      //sylma.log(this.getParents());
    }
  },

  initObjects : function(objects) {

    for (var key in objects) {

      this.initObject(objects[key], key);
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

      result = this.initObjectSplice(this.tmp, typeOf(key) === 'number' ? key : undefined);
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

      this.nodes[key] = node; //.length ? node[0] : node;

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

    if (debug === undefined) debug = false;

    if (!this.objects[name]) {

      if (debug) throw new Error('No object named ' + name);
      result = null;
    }
    else {

      result = this.objects[name];
    }

    return result;
  },

  send : function(path, args, callback, get) {

    return sylma.ui.send(path, args, callback, get);
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

        // TODO : node.destroy() send "too much recursion" error, maybe too much events
        node.dispose();
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

    return parent && parent.destroyChild(this.getKey());
  },

  destroyChild : function(key) {

    if (key === undefined) {

      throw new Error('Undefined key for destroy');
    }

    if (typeof(key) === 'number') {

      this.tmp.splice(key, 1);

      this.tmp.slice(key).each(function(item, sub) {

        item.setKey(sub + key);
      });
    }
    else {

      delete this.objects[key];
    }
  }
});

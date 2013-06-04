/* Document JS */

var sylma = {};

sylma.modules = {};

sylma.binder = {
  classes : {},
  objects : {}
};

sylma.classes = {

  ui : new Class({

    tmp : {},

    load : function(parent, objects) {

      var length = objects.length;

      if (length > 1) {

        this.loadMultiple(objects, parent);
      }
      else {

        this.loadOne(objects, parent);
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

    addMessage : function(content, container) {

      container = container || $('messages');
      container.adopt(this.import(content));
    },

    parseMessages : function(result, container) {

      if (result.messages) {

        for (var i in result.messages) {

          this.addMessage(result.messages[i].content, container);
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

      var obj;

      for (var key in objects) {

        objects[key].parentObject = this;
        obj = ui.createObject(objects[key]);

        if (objects[key].name) this.objects[key] = obj;
        else this.tmp.push(obj);
      }
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

      return this.objects[name];
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

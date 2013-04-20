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

    loadOne : function(objects, parent) {

      for (var first in objects) break;
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

    parseResult : function(result) {

      if (result.messages) {

        var el = new Element('div', {
          html : result.messages,
        });

        $('messages').adopt(el.getChildren());
      }

      return result.result;
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

    initialize : function(props) {

      props = this.loadProperties(props);

      this.initBasic(props);

      if (props.options) this.initOptions(props.options);
      if (props.objects) this.initObjects(props.objects);
      if (props.events) this.initEvents(props.events);
      if (props.nodes) this.initNodes(props.nodes);
    },

    loadProperties : function(props) {

      return Object.merge(props, sylma.binder.classes[props.binder]);
    },

    initObjects : function(objects) {

      var obj;

      for (var key in objects) {

        objects[key].parent = this;
        obj = ui.createObject(objects[key]);

        if (objects[key].name) this[key] = obj;
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

      if (event.target) {

        nodes = this.getNode().getElements('.' + event.target);
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

        this[option] = options[option]
      }
    },

    initBasic : function(options) {

      if (!options.id) throw 'No node associated';

      this.node = $(options.id);
      this.prepareNodes(this.node);
    },

    prepareNodes : function(nodes) {

      $$(nodes).store('sylma-object', this);
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
    }
  });


}).call(sylma.ui);

/* Document JS */

var sylma = {};

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

      path.split('.').each(function(item) { result = result[item] });

      return result;
    },

    loadMultiple : function(objects, parent) {

      for (var obj in objects) {

        parent[obj] = this.createObject(objects[obj]);
      }
    },

    loadOne : function(objects) {

      for (var first in objects) break;
      parent[first] = this.createObject(objects[first]);
    },

    createObject : function(options) {

      var parent = this.loadPath(options.extend);

      return new parent(options);
    }
  })
}

sylma.ui = new sylma.classes.ui;

(function() {

  var ui = this;

  this.Base = new Class({

    Implements : Options,

    node : null,
    options : {

    },

    initialize : function(options) {

      options = this.loadOptions(options);

      this.initBasic(options);

      if (options.properties) this.initObjects(options.properties);
      if (options.objects) this.initObjects(options.objects);
      if (options.events) this.initEvents(options.events);
    },

    loadOptions : function(options) {

      return Object.merge(options, sylma.binder.classes[options.binder]);
    },

    initObjects : function(objects) {

      for (var key in objects) {

        this[key] = ui.createObject(objects[key]);
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

    initProperties : function(properties) {

      this.initPropertiesBasic(properties.basic);
      delete(properties.basic);

      for (var prop in properties) {

        this[prop] = properties[prop]
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
    getNode : function() {

      return this.node;
    }
  });

  this.Test = new Class({
    Extends : this.Base,
    test : function() {
      alert('test');
    }
  });

}).call(sylma.ui);

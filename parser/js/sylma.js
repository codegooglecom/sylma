/* Document JS */

sylma = {};

sylma.binder = {
  classes : {},
  objects : {}
};

sylma.ui = {};

(function() {

  this.load = function(objects) {

    var length = objects.length;

    if (length > 1) {

      this.tmp = [];

      for (var obj in objects) {

        this.tmp.push(this.createObject(obj));
      }

      if (objects.root) this.root = this.createObject(objects.root);
    }
    else {

      for (var first in objects) break;
      this.root = this.createObject(objects[first]);
    }
  }

  this.createObject = function(options) {

    var parent = window;
    options.extend.split('.').each(function(item, index) { parent = parent[item] });

    return new parent(options);
  }

  this.Base = new Class({

    Implements : Options,
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

    },

    initEvents : function(events) {

      for (var name in events) {

        this.initEvent(name, events[name]);
      }
    },

    initEvent : function(name, event) {

      var nodes = event.target ? this.getNode().getElements('.' + event.target) : this.getNode();
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

      this.node = document.id(options.id);
      $(this.node).store('sylma-object', this);
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
  })

}).call(sylma.ui);

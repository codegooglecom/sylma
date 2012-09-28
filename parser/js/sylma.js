/* Document JS */

sylma = {};

sylma.binder = {
  classes : {},
  objects : {}
};

sylma.ui = {};

(function() {

  this.Base = new Class({

    Implements : Options,
    options : {

    },

    initialize : function(options) {

      options = this.loadOptions(options)

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

      for (var e in events) {

        this.initEvent(events[e]);
      }
    },

    initEvent : function(event) {

      var nodes = event.target ? this.getNode().getElements('.' + event.target) : this.getNode();
      nodes.addEvent(event.name, event.callback);
    },

    initProperties : function(properties) {

      this.initPropertiesBasic(properties.basic);
      delete(properties.basic);

      for (var prop in properties) {

        this[prop] = properties[prop]
      }
    },

    initPropertiesBasic : function(options) {

      if (!options.id) throw 'No node associated';

      this.node = document.id(options.id);
    },

    /**
     * @return Element
     */
    getNode : function() {

      return this.node;
    }
  });

}).call(sylma.ui);

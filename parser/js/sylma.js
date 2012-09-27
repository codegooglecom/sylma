/* Document JS */

sylma = {
  binder : {},
  ui : function() {

    this.Object = new Class({

      Implements : Options,
      options : {

      },

      initialize : function(options) {

        if (options.properties) this.initObjects(options.properties);
        if (options.objects) this.initObjects(options.objects);
        if (options.events) this.initEvents(options.events);
      },



      initObjects : function(objects) {

      },

      initEvents : function(events) {

      },

      initProperties : function(properties) {

      }
    });
  }
}

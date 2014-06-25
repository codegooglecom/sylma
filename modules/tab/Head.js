
sylma.ui.tab.Head = new Class({

  Extends : sylma.ui.Base,

  options : {
    mode : 'inside'
  },

  onReady : function() {

    var mode = this.options.mode;

    this.tmp.each(function(item) {

      if (!item.options.mode) {

        item.options.mode = mode;
      }
    });

    this.getNode().addClass('sylma-tab-head');
  },

  downlightAll : function() {

    var len = this.tmp.length;

    for (var i = 0; i < len; i++) {

      this.tmp[i].downlight();
    }
  },

  getCaller : function(index) {

    return this.tmp[index];
  }

});

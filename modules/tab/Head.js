
sylma.ui.tab.Head = new Class({

  Extends : sylma.ui.Base,

  initialize : function(props) {

    this.parent(props);
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

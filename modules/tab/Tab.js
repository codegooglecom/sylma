
sylma.ui.tab.Tab = new Class({

  Extends : sylma.crud.Group,
  width : 0,
  position : 0,

  initialize : function(options) {

    this.parent(options);
    this.getNode().addClass('sylma-tab');
  },

  prepare : function(width, position) {

    this.width = width;
    this.position = position;

    this.prepareNode();
  },

  prepareNode : function() {

    this.getNode().setStyle('width', this.width);
  },

  updateSuccess : function(response) {

    this.parent(response);
    this.prepareNode();
  },

  needUpdate : function() {

    return this.get('path') && !this.getNode().getChildren().length;
  },

  show : function() {

    if (this.needUpdate()) {

      this.update({}, this.get('path'));
    }
  },

  getCaller : function() {

    return this.getParent(1).getHead().getCaller(this.position);
  }

});


sylma.crud.fieldset.RowForm = new Class({

  Extends : sylma.crud.fieldset.RowMovable,

  updatePositionInput : function(val) {

    this.getNode('position').set('value', val);
  },

  release: function() {

    this.parent();
    this.updatePositions();
  },

  updatePositions: function() {

    var node = this.getNode();

    node.getParent().getChildren().each(function(el, key) {

      var obj = el.retrieve('sylma-object');
      obj.updatePositionInput(key + 1);
    });
  }

});
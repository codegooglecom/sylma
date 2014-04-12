
sylma.crud.list.Filter = new Class({

  Extends : sylma.ui.Container,

  update : function() {

    this.getParent('table').update(true, true);
  },

  updateSize : function(width) {

    this.getNode().getElements('input, select').setStyle('width', width - 6);
  },

  clear : function() {

    var nodes = this.getNode().getElements('input, select');

    nodes.set('value', '');
    nodes.fireEvent('input');
    nodes.fireEvent('change');

    nodes.each(function(node) { node.focus() });
  }
});

sylma.crud.list.Filters = new Class({

  Extends : sylma.ui.Container,
  
  onLoad : function() {

    this.updateSize();
    this.getParent('table').addEvent('complete', this.updateSize.bind(this));
  },

  updateSize : function() {

    var filters = this.tmp;
    var table = this.getParent('table').getNode('table');

    //this.getNode().setStyle('width', table.getStyle('width'));

    table.getElements('thead tr > *').each(function(item, key) {

      filters[key].updateSize(item.getStyle('width').toInt() + 2);
    });
  }
});
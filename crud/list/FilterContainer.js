
sylma.crud.list.FilterContainer = new Class({

  Extends : sylma.ui.Container,

  updateSize : function(width) {

    this.getNode().setStyle('width', width);
    this.tmp.each(function(item) {

      item.updateSize(width);
    });
  }
});
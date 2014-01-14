
sylma.crud.list.Table = new Class({

  Extends : sylma.ui.Container,

  initialize : function(props) {

    this.parent(props);
    this.getObject('head').tmp.each(function(head) {
      head.updateOrder();
    });
  }
});


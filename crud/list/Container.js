sylma.crud = {};
sylma.crud.list = {};

sylma.crud.list.Container = new Class({

  Extends : sylma.ui.Container,

  update : function(args) {

    if (this.get('send')) {

      args = Object.merge(this.get('send'), args);
    }

    return this.parent(args);
  }
});
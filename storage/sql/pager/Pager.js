
sylma.crud.Pager = new Class({

  Extends : sylma.ui.Container,

  setPage : function(key) {

    this.getInput().set('value', key);
  },

  getInput : function() {

    return this.getNode('input');
  },

  goPage : function(val, e) {

    this.getInput().set('value', val);
    this.getParent('table').submit();

    e.preventDefault();
  }
});
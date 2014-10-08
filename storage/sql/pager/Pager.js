
sylma.crud = sylma.crud || {};

sylma.crud.Pager = new Class({

  Extends : sylma.ui.Container,

  setPage : function(key) {

    this.getInput().set('value', key);
  },

  getInput : function() {

    return this.getNode('input');
  },

  getValue : function() {

    return this.getNode('input').get('value');
  },

  goPage : function(val, e) {

    this.getInput().set('value', val);
    this.getParent('table').submit();

    e.preventDefault();
  }
});
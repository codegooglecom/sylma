
sylma.crud.list.Row = new Class({

  Extends : sylma.ui.Base,

  onClick : function(e) {

    if (['A', 'BUTTON'].indexOf(e.target.tagName) > -1) {

      return true;
    }

    this.show();
  },

  show : function() {

    window.location = this.get('url');
  }
});

sylma.crud = sylma.crud || {} ;

sylma.crud.FormSub = new Class({

  Extends : sylma.crud.Form,

  getHandler: function() {

    return this.getParent('handler');
  },

  submit : function(e, args, callback) {

    this.getHandler().submit(e, args, callback);

    if (e) {

      e.preventDefault();
    }
  },

  deleteConfirm : function() {

    this.getHandler().deleteConfirm();
  }
});

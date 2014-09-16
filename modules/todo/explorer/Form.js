
sylma.modules.todo.Form = new Class({

  Extends : sylma.crud.FormAjax,

  submit : function(e, args, callback) {

    this.parent(e, args, callback);
  },

  showMask : function() {

    this.getParent('side').startLoading();
  },

  hideMask : function() {

    this.getParent('side').stopLoading();
  },

  submitSuccess : function() {

    this.hideMask();
    this.getParent('task').toggleSide(true, true, false);
    this.getParent('explorer').updateCollection();
  },

  getMode : function() {

    return this.options.mode;
  },

  cancel : function () {

    this.getParent('task').toggleSide(false, true);

    if (this.getMode() === 'insert') {

      this.getParent('task').remove();
    }
  }
});
sylma.crud = sylma.crud || {} ;

sylma.crud.FormMaster = new Class({

  Extends : sylma.ui.Container,

  updateList : function() {

    this.getParent('tab').getObject('list').update();
  },

  deleteItem : function() {

    this.show(this.getNode('delete'));
  },

  deleteConfirm : function() {

    this.getObject('local').deleteSend(function() {

      this.getParent('container').hide();
      this.updateList();

    }.bind(this));
  },

  deleteCancel : function() {

    this.hide(this.getNode('delete'));
  },

  cancel : function() {

    this.getParent('container').hide();
  }
});

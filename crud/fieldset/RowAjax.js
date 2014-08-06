
sylma.crud.fieldset.RowAjax = new Class({

  Extends : sylma.crud.fieldset.RowMovable,

  edit : function(callback) {

    var path = this.getParent('fieldset').get('update');
    var form = this.getParent('tab').getObject('form');

    form.update({
      id : this.get('id')
    }, path, false, callback);
  },

  remove : function() {

    var position = this.position;

    this.parent();

    this.send(this.getParent('fieldset').get('delete'), {
      id : this.get('id')
    });
  },

  getParentID : function() {

    return this.getParent('fieldset').get('parent');
  },

  release : function() {

    this.parent();

    var reset = false;
    var from = this.scroll.from;
    var to = this.scroll.current;

    if (reset || from !== to) {

      this.send(this.getParent('fieldset').get('move'), {
        id : this.get('id'),
        position : to
      });
    }
  }
});


sylma.crud.Text = new Class({

  Extends : sylma.crud.Field,

  initialize : function(props) {

    this.parent(props);
    var input = this.getNode('input');

    if (input.get('value') && input.get('value').match(/^\s*$/)) {
      input.set('value');
    }
  },

  setValue : function(val) {

    this.getInput().set('value', val);
    //this.getInput().refresh();
  }
});

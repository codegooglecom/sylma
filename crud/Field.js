
sylma.crud.Field = new Class({

  Extends : sylma.ui.Clonable,
  highlightClass : 'field-statut-invalid',

  setValue : function(val) {

    val = val === undefined ? '' : val;

    this.getInput().set('value', val);
  },

  getValue : function() {

    return this.getInput().get('value');
  },

  getInputs : function() {

    return this.getNode().getElements('input, select, textarea');
  },

  getInput : function() {

    return this.getNode('input', false) || this.getNode().getElement('input, select, textarea');
  },

  resetHighlight : function() {


  },

  isHighlighted : function() {

    return this.getNode().hasClass(this.highlightClass);
  },

  highlight : function() {

    this.getNode().addClass(this.highlightClass);
  },

  downlight : function() {

    var name = this.highlightClass;

    if (this.isHighlighted()) {

      this.getNode().removeClass(name);
      this.getParent().downlight();
    }
  },

  clonePrepare : function() {

    //if (!this.props.sylma.isclone) {

      this.parent();

      var input = this.getInput();

      input.set('data-name', input.get('name'));
      input.set('name');
    //}
  },

  clone : function(parent, node, position) {

    var result = this.parent(parent, node);
    result.cloneInput(position);

    return result;
  },

  cloneContent : function(objects, tmp) {

  },

  cloneInput : function(position) {

    var input = this.getInput();

    var name = input.get('data-name').replace(/\[0\]/, '[' + position + ']');

    input.set('name', name);
    input.set('id', name);

    var label = this.getNode().getElement('label', false);
    if (label) label.set('for', name);
  },
});

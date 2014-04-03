
sylma.crud.Field = new Class({

  Extends : sylma.ui.Base,
  highlightClass : 'field-statut-invalid',

  initialize : function(props) {

    this.parent(props);
    this.props = props;

    var inputs = this.getInputs();

    if (this.change && inputs) {

      this.prepareNodes(inputs);
      inputs.addEvent('change', this.change.callback);
    }
  },

  initEvent : function(event) {

    if (event.name === 'change') {

      this.change = event;
    }
    else {

      this.parent(event);
    }
  },

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

    var id = 'sylma' + Math.floor(Math.random(new Date().getSeconds()) * 999);
    this.getNode().addClass(id);

    var input = this.getInput();
    input.set('data-name', input.get('name'));
    input.set('name');

    this.cloneID = id;
  },

  clone : function(parent, node, position) {

    var props = this.props;

    props.node = node.getElements('.' + this.cloneID).pick();
    props.id = null;
    props.parentObject = parent;

    var result = sylma.ui.createObject(props);
    result.updateID(position);

    return result;
  },

  updateID : function(id) {

    var input = this.getInput();
    var name = input.get('data-name').replace(/\[\]/, '[' + id + ']');
    input.set('name', name);
    input.set('id', name);
    var label = this.getNode().getElement('label', false);
    if (label) label.set('for', name);
  },
});

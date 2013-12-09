sylma.stepper.Property = new Class({

  Extends : sylma.stepper.Framed,

  onLoad: function() {

    if (this.options.name) {

      this.getSelect().set('value', this.options.name);
    }
  },

  getSelect : function() {

    return this.getNode('name');
  },

  getName: function() {

    return this.options.name;
  },

  onChange : function() {

    var el = this.getParent().getSelector().getElement();
    var key = this.getSelect().getSelected().get('value')[0];
    var result;

    switch (key) {

      case 'children' :

        result = el.getChildren().length;

        break;

      default :

        result = el.getStyle(key).toInt();
    }

    this.options.name = key;
    this.options.value = result;

    this.updateValue();
  },

  test : function(el) {

    var value = this.options.value.toInt();
    var result;

    switch (this.options.name) {

      case 'children' :

        result = el.getChildren().length === value;
        break;

      default :

        result = el.getComputedStyle(this.options.name).toInt() === value;
    }

    return result;
  },

  updateValue : function() {

    this.getNode('value').set('html', this.options.value);
  },

  toJSON : function() {

    return {
      '@name' : this.options.name,
      0 : this.options.value
    };
  }
});

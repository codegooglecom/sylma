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
    var style = this.getSelect().getSelected().get('value')[0];

    this.options.name = style;
    this.options.value = el.getStyle(style).toInt();

    this.updateValue();
  },

  test : function(el) {

    return el.getComputedStyle(this.options.name).toInt() === this.options.value.toInt();
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

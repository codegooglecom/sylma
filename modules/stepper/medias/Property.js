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

      case 'class' :

        result = el.get('class');
        break;

      case 'iframe' :

        result = 1;
        break;

      default :

        result = el.getStyle(key).toInt();
    }

    this.options.name = key;
    this.options.value = result;

    this.updateValue();
  },

  refresh : function() {

    this.onChange();
    this.getParent().hasError(false);
  },

  reset : function() {

    this.ready = false;
    this.binded = false;
  },

  onFrameLoad : function(el) {

    el.removeEvent('load', this.onFrameLoad);
    this.ready = true;
  },

  test : function(el) {

    var value = this.options.value;
    var result;

    switch (this.options.name) {

      case 'children' :

        result = el.getChildren().length === value.toInt();
        break;

      case 'class' :

        result = el.hasClass(value);
        break;

      case 'iframe' :

        if (!this.binded) {

          this.binded = true;
          el.addEvent('load', this.onFrameLoad.bind(this, el));
        }

        result = this.ready;
        break;

      default :

        result = el.getComputedStyle(this.options.name).toInt() === value.toInt();
    }

    return result;
  },

  updateValue : function(val) {

    if (val !== undefined) this.options.value = val;
    this.getNode('value').set('value', this.options.value);
  },

  toJSON : function() {

    return {
      '@name' : this.options.name,
      0 : this.options.value
    };
  }
});


sylma.crud.collection.Head = new Class({

  Extends : sylma.ui.Base,

  options : {
    dir : false,
    current : false
  },

  highlight : function() {

    this.parent();
    this.set('current', true);
  },

  downlight : function() {

    this.parent();
    this.set('current', false);
  },

  updateDir : function(dir) {

    dir = dir || false;

    this.set('dir', dir);
    this.getNode().toggleClass('order-desc', dir).blur();
  },

  update : function() {

    var current = this.get('current');

    this.getParent('head').downlight();

    if (current) this.updateDir(!this.get('dir'));
    else this.updateDir(false);

    this.updateContainer();
    this.highlight();

    return false;
  },

  updateContainer: function () {

    this.getParent('head').updateContainer(this, this.options.name, this.options.dir);
  }
});


sylma.crud.list.Head = new Class({

  Extends : sylma.ui.Base,

  options : {
    dir : false,
    current : false
  },

  updateOrder : function() {

    var order = this.extractOrder();

    if (order && order.name === this.get('name')) {

      this.updateDir(order.dir);
      this.highlight();
    }
  },

  extractOrder : function() {

    var result = {};
    var name = this.getContainer().getNode('order').get('value');

    if (name) {

      if (name[0] === '!') {

        result.name = name.substr(1);
        result.dir = 1;
      }
      else {

        result.name = name;
      }
    }

    return result;
  },

  highlight : function() {

    this.parent();
    this.set('current', true);
  },

  downlight : function() {

    this.parent();
    this.set('current', false);
  },

  getContainer : function() {

    return this.getParent('table');
  },

  updateDir : function(dir) {

    dir = dir || false;

    this.set('dir', dir);
    this.getNode().toggleClass('order-desc', dir).blur();
  },

  update : function() {

    var current = this.get('current');

    this.getParent().tmp.each(function(head) {
      head.downlight();
    });

    //var container = this.getContainer();

    if (current) this.updateDir(!this.get('dir'));
    else this.updateDir(false);

    //container.update({order : (this.get('dir') ? '!' : '') + this.get('name')});

    var table = this.getContainer();

    table.getNode('order').set('value', (this.get('dir') ? '!' : '') + this.get('name'));
    table.update(true);

    this.highlight();

    return false;
  }
});

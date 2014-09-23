
sylma.crud = sylma.crud || {};
sylma.crud.collection = sylma.crud.collection || {};

sylma.crud.collection.Sorter = new Class({

  Extends : sylma.ui.Base,

  current : [],
  //orders : {},

  onLoad : function() {

    var order = this.extractOrders().pick();

    var name = order.name;
    var dir = order.dir;

    this.tmp.each(function(head) {

      //this.orders[head.options.name] = head;

      if (name === head.get('name')) {

        head.updateDir(dir);
        head.highlight();
      }
    });
  },

  getValue : function() {

    return this.getParent('table').getNode('order').get('value');
  },

  downlight : function() {

    this.tmp.each(function(head) {
      head.downlight();
    });
  },

  extractOrders : function() {

    var content = this.getValue();
    this.current = [];

    if (content) {

      content.split(',').map(String.trim).each(function(val) {

        this.current.push(this.extractOrder(val));

      }.bind(this));
    }

    return this.current;
  },

  extractOrder : function(name) {

    var result = {};

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

  buildValues : function(orders) {

    var vals = orders.map(function(order) {

      return this.buildValue(order.name, order.dir);

    }.bind(this));

    return vals.join(',');
  },

  buildValue: function (name, dir) {

    return (dir ? '!' : '') + name;
  },

  updateMultiple : function(name, dir) {

    var orders = this.current;

    orders = orders.filter(function(order) {

      return order.name !== name;
    });

    orders.unshift({
      name : name,
      dir : dir
    });

    this.current = orders.slice(0, 3);

    return this.current;
  },

  updateContainer: function (name, dir) {

    var table = this.getParent('table');

    var orders = this.updateMultiple(name, dir);
    var content = this.buildValues(orders);

    table.getNode('order').set('value', content);
    table.update(true);
  }
});

sylma.stepper = sylma.stepper || {};

sylma.stepper.Listed = new Class({

  currentKey : -1,

  setCurrent : function(key) {

    this.currentKey = key || 0;
  },

  getCurrent : function() {

    return this.currentKey;
  },

  getItems : function() {

    throw new Error('Must be overrided');
  },

  goNext : function() {

    var result = false;
    var items = this.getItems();
    var current = this.getCurrent();

    if (current < 0) {

      var item = items.pick();

      if (item) {

        this.setCurrent();
        this.showItem(item);

        result = true;
      }
    }
    else {

      result = this.checkItem(items[current]);

      if (!result) {

        current++;

        if (current !== items.length) {

          this.setCurrent(current);
          this.showItem(items[current]);

          result = true;
        }
      }
    }

    return result;
  },

  showItem : function(item) {

    item.go(function() {

      item.goNext();
      
    }, true);
  },

  checkItem : function(item) {

    return item.goNext();
  },

  testLast : function(items, key, callback) {

    this.testItem(items, key, callback);
  },

  testItems : function(items, key, callback, record) {

    key = key || 0;

    items[key].test(function() {

      this.testNextItem(items, key + 1, callback, record);

    }.bind(this));
  },

  testNextItem : function(items, key, callback, record) {

    var length = items.length;

    if (key === length - 1) {

      this.testLast(items, key, callback, record);
    }
    else if (key < length) {

      this.testItem(items, key, callback, record);
    }
    else if (callback)  {

      callback();
    }
  },

  /**
   * Available for override
   */
  testItem : function(items, key, callback, record) {

    this.testItems(items, key, callback, record);
  }
});

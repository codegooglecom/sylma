
sylma.stepper.Collection = new Class({

  Extends : sylma.stepper.Framed,
  Implements : sylma.stepper.Listed,

  items : [],

  onLoad : function() {

    this.loadDirectories();
/*
    var items = [];

    items.append(this.getObject('directory'));
    items.append(this.getObject('module'));
console.log(this);
    this.items = items;
*/
  },

  loadDirectories : function() {

    //this.addChildren(this.get('items'));
    this.setCurrent(-1);
  },

  loadDirectory : function(path, callback) {

    this.send(this.getParent('main').get('loadDirectory'), {
      dir : path
    }, callback);
  },

  getItems : function() {

    return this.tmp;
  },

  goItem : function(test) {

    var key = test.getKey();
    this.setCurrent(key);

    this.getItems().each(function(item, sub) {

      if (sub !== key) item.toggleSelect(false);
    });
  },

  toggleSelect : function(val, callback) {

    callback && callback();
  },

  createTest : function() {

    return this.getItem().createTest();
  },

  getTest : function() {

    return this.getItem().getTest();
  }

});
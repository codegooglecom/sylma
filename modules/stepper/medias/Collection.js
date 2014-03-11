
sylma.stepper.Collection = new Class({

  Extends : sylma.stepper.Framed,
  Implements : sylma.stepper.Listed,

  onLoad : function() {

    this.loadDirectories();
  },

  loadDirectories : function() {

    var dirs = this.get('items');

    if (dirs) {

      Object.each(dirs.directory, function(item) {

        this.add('directory', item);

      }.bind(this));
    }

    this.setCurrent(-1);
  },

  loadDirectory : function(path, callback) {

    this.send(this.getParent('main').get('loadDirectory'), {
      dir : path
    }, callback);
  },

  getItems : function() {

    return this.getObject('directory');
  },

  goDirectory : function(test) {

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
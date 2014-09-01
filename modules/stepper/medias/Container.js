
sylma.stepper.Container = new Class({

  Extends : sylma.stepper.Framed,
  Implements : sylma.stepper.Listed,

  getContainer: function() {

    return this.getNode('items');
  },

  toggleSelect : function(val, callback) {

    var el = this.getContainer();

    this.toggleActivation(val);

    if (this.toggleShow(el, val)) {

      this.initItems(function() {

        this.getParent().goItem(this);
        callback && callback();
        
      }.bind(this));

    }
    else {

      this.setCurrent(-1);

      this.getItems().each(function(item) {

        item.toggleSelect(false);
      });
    }
  },

  loadItem : function() {

    throw new Error('Must be overrided');
  },

  initItems : function(callback) {

    if (!this.loaded && !this.loading) {

      this.loading = true;

      this.loadItems(function(response) {

        if (!response.error) {

          this.loaded = true;
        }

        this.loading = false;
        this.addChildren(response.content);

        callback && callback();

      }.bind(this));
    }
    else {

      callback && callback();
    }
  },

  getItems : function() {

    throw new Error('Must be overrided');
  },

  goItem : function(item) {

    var key = item.getKey();
    this.setCurrent(key);

    this.getItems().each(function(item, sub) {
//console.log(item.node, sub !== key);
      if (sub !== key) item.toggleSelect(false);
    });
  },
});
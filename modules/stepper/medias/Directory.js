
sylma.stepper.Directory = new Class({

  Extends : sylma.stepper.Framed,
  Implements : sylma.stepper.Listed,

  loaded : false,
  loading: false,

  path : '',

  getPath : function() {

    return this.get('path');
  },

  toggleSelect : function(val, callback) {

    var el = this.getContainer();

    this.toggleActivation(val);

    if (this.toggleShow(el, val)) {

      this.initTests(callback);
      this.getParent('collection').goDirectory(this);
    }
    else {

      this.setCurrent(-1);

      this.getTests().each(function(item) {

        item.toggleSelect(false);
      });
    }
  },

  getContainer: function() {

    return this.getNode('items');
  },

  initTests : function(callback) {

    if (!this.loaded && !this.loading) {

      this.loading = true;

      this.getParent('collection').loadDirectory(this.get('path'), function(response) {

        if (!response.error) {

          this.loaded = true;
        }

        this.loading = false;

        if (response.content) {

          Object.each(response.content.test, function(item) {

            this.add('test', item);

          }.bind(this));
        }

        callback && callback();

      }.bind(this));
    }
    else {

      callback && callback();
    }
  },

  createTest : function() {

    var result = this.addTest({}, true);

    result.toggleSelect(true);
    this.getMain().record(true);
  },

  addTest : function(props, nofile) {

    props.nofile = nofile;

    var result = this.add('test', props);
    this.setCurrent(result.getKey());

    return result;
  },

  getTests : function(debug) {

    return this.getObject('test', debug)  || [];
  },

  getItems : function() {

    return this.getTests();
  },

  getTest : function(debug) {

    var tests = this.getTests(debug);

    return tests && tests[this.getCurrent()];
  },

  goTest : function(test) {

    var key = test.getKey();
    this.setCurrent(key);

    this.getTests().each(function(item, sub) {

      if (sub !== key) item.toggleSelect(false);
    });
  },

  loadTest : function(file, callback) {

    this.send(this.getParent('main').get('loadTest'), {
      path : file,
      dir : this.getPath()
    }, callback);
  }
});
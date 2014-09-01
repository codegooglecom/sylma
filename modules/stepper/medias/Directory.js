
sylma.stepper.Directory = new Class({

  Extends : sylma.stepper.Container,

  loaded : false,
  loading: false,

  path : '',

  getPath : function() {

    return this.get('path');
  },

  createTest : function() {
console.log(this);
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

  loadItems : function(callback) {

    return this.getParent('collection').loadDirectory(this.getPath(), callback);
  },

  loadTest : function(file, callback) {

    this.send(this.getParent('main').get('loadTest'), {
      path : file,
      dir : this.getPath()
    }, callback);
  }
});
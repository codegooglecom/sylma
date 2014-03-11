
sylma.stepper.DirectoryStandalone = new Class({

  Extends : sylma.stepper.Directory,

  sylma : {
    template : {
      classes : {}
    }
  },

  setupTemplate : function(_class, el) {

    this.sylma.template.classes.test = _class;
    this.sylma.parents.directory = this;

    this.initNode({node : el});

    sylma.ui.loadArray([this]);
  },

  toggleSelect : function(val, callback) {

    callback && callback();
  },

  loadTests : function() {

    var tests = this.get('tests');

    if (tests) {

      Object.each(tests.test, function(item) {

        this.addTest(item);

      }.bind(this));
    }

    this.setCurrent(-1);
  },

});
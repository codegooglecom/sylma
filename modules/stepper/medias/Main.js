sylma.stepper = sylma.stepper || {};

sylma.stepper.Main = new Class({

  Extends : sylma.ui.Container,
  Implements : sylma.stepper.Listed,

  screen : {
    x : 1280,
    y : 1024
  },

  onReady : function() {

console.log(this.get('tests'));

    if (!this.getTest(false)) {

      var props = Object.merge(this.get('tests').test[0], {
        file : this.get('file')
      });

      this.addTest(props);
    }

    this.buildFrame('/');
  },

  getFrame : function() {

    return this.getNode('frame');
  },

  getWindow : function() {

    return this.getFrame().contentWindow;
  },

  buildFrame : function(url) {

    var result = this.getFrame();
    var win = this.getWindow();

    result.set({
      src       : url,
      styles : {
        width : this.screen.x,
        height : this.screen.y
      }
      /*,
      events : {
        load : function() {

          this.setStyle('height', $(win.document.body).scrollHeight);
        }
      }*/
    });

    result.addClass('sylma-visible');

    return result;
  },

  resetFrame : function() {

    this.getFrame().removeEvents();
  },

  addTest : function(props) {

    var result = this.add('test', props);
    this.setCurrent(result.getKey());

    return result;
  },

  getTests : function(debug) {

    return this.getObject('test', debug);
  },

  getTest : function(debug) {

    var tests = this.getTests(debug);

    return tests && tests[this.currentKey];
  },

  record : function() {

    this.resetFrame();
    var test = this.getTest();

    test.record();
  },

  addWatcher : function() {

    this.getTest().getPage().addWatcher();
  },

  test : function() {

    this.resetFrame();

    this.testItems(this.getTests(), 0, function() {

      sylma.ui.showMessage('All tests passed');
    });
  },

  save : function() {

    var test = JSON.stringify(this.getTests());
//console.log(test); return;
    this.send(this.get('path'), {
      file : this.get('file'),
      test : test
    });
  }

});
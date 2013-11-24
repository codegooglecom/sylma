sylma.stepper = sylma.stepper || {};
sylma.factory.debug = true;
sylma.debug.log = true;

sylma.stepper.Main = new Class({

  Extends : sylma.ui.Container,
  Implements : sylma.stepper.Listed,

  screen : {
    x : 1280,
    y : 1024
  },

  recording : false,

  onReady : function() {

sylma.log(this.get('tests'));

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

    if (!this.recording) {

      this.getTest().record();
      this.recording = true;
    }
    else {

      this.getFrame().removeEvents();
      this.getWindow().removeEvents();
      this.recording = false;
    }

    this.getNode().toggleClass('record', this.recording);
  },

  pauseRecord: function() {

    if (this.recording) {

      this.getTest().stopCapture();
    }
  },

  resumeRecord: function() {

    if (this.recording) {

      this.getTest().startCapture();
    }
  },

  addWatcher : function() {

    this.getTest().getPage().addWatcher();
  },

  test : function() {

    this.pauseRecord();

    this.testItems(this.getTests(), 0, function() {

      sylma.ui.showMessage('All tests passed');
    });
  },

  save : function() {

    var test = JSON.stringify(this.getTests());
//sylma.log(test); return;
    this.send(this.get('path'), {
      file : this.get('file'),
      test : test
    });
  }

});
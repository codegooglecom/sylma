sylma.stepper = sylma.stepper || {};
sylma.factory.debug = false;
sylma.debug.log = true;

sylma.stepper.Main = new Class({

  Extends : sylma.ui.Container,
  Implements : sylma.stepper.Listed,

  path : '/',

  screen : {
    x : 1280,
    y : 1024
  },

  recording : false,
  events : {},

  onReady : function() {

    var tests = this.get('tests');

    if (tests) {

      Object.each(tests.test, function(item) {

        this.addTest(item);

      }.bind(this));
    }

    this.setCurrent(-1);
    this.buildFrame(this.path);
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

  createTest : function() {

    var result = this.addTest({}, true);

    result.toggleSelect(true);
    this.record(true);
  },

  addTest : function(props, nofile) {

    props.nofile = nofile;

    var result = this.add('test', props);
    this.setCurrent(result.getKey());

    return result;
  },

  getTests : function(debug) {

    return this.getObject('test', debug);
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

  record : function(force) {

    if (force || !this.recording) {

      this.getTest().record(this.startCapture.bind(this));
      this.recording = true;
    }
    else {

      this.getFrame().removeEvents();
      this.getWindow().removeEvents();
      this.recording = false;
    }

    this.getNode().toggleClass('record', this.recording);
  },

  isInput : function(el) {

    var tag = el.get('tag');

    return ['input','textarea'].indexOf(tag) > -1 && ['checkbox', 'radio', 'button'].indexOf(el.getAttribute('type')) === -1;
  },

  preparePage : function(callback) {

    var frame = this.getFrame();

    this.events.test = function() {

      frame.removeEvent('load', this.events.test);
      if (callback) callback();

    }.bind(this);

    frame.removeEvent('load', this.events.test);
    frame.addEvent('load', this.events.test);
  },

  addInput : function(e) {

    var target = e.target;

    if (!this.input || this.input.getElement() != target) {

      this.input = this.getTest().getPage().addInput(e);
    }

    this.input.updateValue();
  },

  startCapture: function() {

    this.stopCapture();

    var test = this.getTest();

    Object.append(this.events, {

      window : {
        click : function(e) {

          var tag = e.target.get('tag');

          if (tag === 'select') {

            //do nothing
          }
          else if (tag === 'option') {

            this.addInput(e);
          }
          else if (!this.isInput(e.target)) {

            this.getTest().getPage().addEvent(e);
            this.input = null;
          }

        }.bind(this),
        keyup : function(e) {

          var target = e.target;

          if (this.isInput(target) && target.get('value')) {

            this.addInput(e);
          }

        }.bind(this)
      },
      frame : {

        load : function() {

          this.addPage();
          this.getPage().addSnapshot();

        }.bind(test)
      }
    });

    var events = this.events;

    this.getFrame().addEvents(events.frame);
    this.getWindow().addEvents(events.window);
  },

  stopCapture: function() {

    var events = this.events;

    this.getFrame().removeEvents(events.frame);
    this.getWindow().removeEvents(events.window);
  },

  pauseRecord: function() {

    if (this.recording) {

      this.stopCapture();
    }
  },

  resumeRecord: function() {

    if (this.recording) {

      this.stopCapture();
      this.startCapture();
    }
  },

  loadTest : function(file, callback) {

    this.send(this.get('load'), {
      file : file,
      dir : this.get('directory')
    }, callback);
  },

  test : function(key) {

    var tests;
    this.pauseRecord();

    if (key === undefined) {
      // only one
      var current = this.getCurrent();
      tests = this.getTests().slice(current < 0 ? 0 : current, current + 1);
    }
    else {
      // all
      tests = this.getTests();
    }

    this.testItems(tests, 0, function() {

      sylma.ui.showMessage('All tests passed');
    });
  },

  save : function() {

    var test = this.getTest().save();
  }

});
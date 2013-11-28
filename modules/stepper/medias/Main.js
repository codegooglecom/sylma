sylma.stepper = sylma.stepper || {};
sylma.factory.debug = true;
sylma.debug.log = true;

sylma.stepper.Main = new Class({

  Extends : sylma.ui.Container,
  Implements : sylma.stepper.Listed,

  path : '/',

  screen : {
    x : 1280,
    y : 1024
  },

  events : {},

  recording : false,
  events : {},

  onReady : function() {

    Object.each(this.get('tests').test, function(item) {

      this.addTest(item);

    }.bind(this));

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

  startCapture: function() {

    this.stopCapture();

    var test = this.getTest();

    var events = this.events = {

      window : {
        click : function(e) {

          if (!this.isInput(e.target)) {

            this.getTest().getPage().addEvent(e);
          }

        }.bind(this),
        keyup : function(e) {

          var target = e.target;

          if (this.isInput(target)) {

            if (!this.input || this.input.getElement() != target) {

              this.input = this.getTest().getPage().addInput(e);
            }

            this.input.updateValue();
          }
        }.bind(this)
      },
      frame : {

        load : function() {

          this.addPage();
          this.getPage().addSnapshot();

        }.bind(test)
      }
    };

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

    this.send(this.get('load'), {file : file}, false, callback);
  },

  test : function() {

    this.pauseRecord();

    this.testItems(this.getTests(), 0, function() {

      sylma.ui.showMessage('All tests passed');
    });
  },

  save : function() {

    var test = this.getTest().save();
  }

});
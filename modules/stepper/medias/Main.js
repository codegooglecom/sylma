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
  pauses : 0,
  events : {},
  variables : {},

  onReady : function() {

    var tests = this.get('tests');

    if (tests) {

      Object.each(tests.test, function(item) {

        this.addTest(item);

      }.bind(this));
    }

    this.setCurrent(-1);
    this.prepareActionFrame();
  },

  getFrame : function() {

    return this.getNode('frame');
  },

  getWindow : function(frame) {

    frame = frame || this.getFrame();

    return frame.contentWindow;
  },

  prepareActionFrame : function() {

    var frame = this.getFrame();
    this.prepareFrame(frame);

    frame.set({
      src : this.path,
      styles : {
        width : this.screen.x,
        height : this.screen.y
      }
    });

    frame.addClass('sylma-visible');
  },

  prepareFrame : function(frame) {

    frame.addEvents({
      load : function() {

        var win = this.getWindow(frame);

        if (!win.addEvents) {

          console.log('Add mootools to iframe');

          var script = document.createElement("script");
          script.type = 'text/javascript';
          script.src = '/sylma/ui/mootools.js';

          script.addEventListener('load', function() {

            this.getFrames(frame).each(function(frame) {

              this.prepareFrame(frame);

            }.bind(this));

            frame.store('ready', 1);
            this.callTest();

          }.bind(this));

          win.document.body.appendChild(script);
        }
        else {

          frame.store('ready', 1);
          this.callTest();
        }

      }.bind(this)
    });
  },

  getFrames : function(frame) {

    return this.getWindow(frame).document.body.getElements('iframe');
  },

  callTest : function() {

    if (this.events.callback) {

      var callback = this.events.callback;
      this.events.callback = undefined;

      callback();
    }
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

  isInput : function(el) {

    var tag = el.get('tag');

    return ['input','textarea'].indexOf(tag) > -1 && ['checkbox', 'radio', 'button', 'submit'].indexOf(el.getAttribute('type')) === -1;
  },

  preparePage : function(callback) {

    this.events.callback = callback;
  },

  addInput : function(e, frames) {

    var target = e.target;

    if (!this.input || this.input.getElement() != target) {

      this.input = this.getTest().getPage().addInput(e, frames);
    }

    this.input.updateValue();
  },

  addVariable : function(item) {

    this.variables[item.getName()] = item;
  },

  getVariable: function(name) {

    if (!this.variables[name]) {

      console.log('Variable "' + name + '"not found');
    }

    return this.variables[name];
  },

  record : function(force) {

    if (force || !this.recording) {

      this.recording = true;
      this.getTest().record(function() {

        this.stopCapture();
        this.startCapture();

      }.bind(this));
    }
    else {

      this.stopCapture();
      this.recording = false;
    }

    this.toggleRecord(this.recording);
  },

  pauseRecord: function() {
//console.log('pause', this.pauses);
    if (this.recording && !this.pauses) {

      this.stopCapture();
    }

    this.pauses++;
  },

  resumeRecord: function() {
//console.log('resume', this.pauses);
    this.pauses--;

    if (this.recording && !this.pauses) {

      this.stopCapture();
      this.startCapture();
    }
  },

  toggleRecord: function(val) {

    this.getNode().toggleClass('record', val);
  },

  startCapture: function() {

    this.stopCapture();

    this.startCaptureFrame(this.getFrame(), this.getTest())
  },

  startCaptureFrame : function(frame, test, token, frames) {

    token = token || 'main';
    frames = frames || [];

    var events = {

      window : {
        click : function(e) {

          var tag = e.target.get('tag');

          if (tag === 'select') {

            //do nothing
          }
          else if (tag === 'option') {

            this.addInput(e, frames);
          }
          else if (!this.isInput(e.target)) {

            this.getTest().getPage().addEvent(e, frames);
            this.input = null;
          }

        }.bind(this),
        keyup : function(e) {

          var target = e.target;

          if (this.isInput(target) && target.get('value')) {

            this.addInput(e, frames);
          }

        }.bind(this)
      },
      frame : {

        load : function() {

          if (frames.length) {

            test.getPage().addWatcher({
              selector : [{
                target : frame
              }],
              property : [{
                name : 'iframe',
                value : 1
              }]
            });
          }
          else {

            test.addPage();
            test.getPage().addSnapshot();
          }
        }.bind(this)
      }
    };

    frames.push(frame);
    var subtoken = 'sub' + frames.length;

    this.getFrames(frame).each(function(item) {

      this.startCaptureFrame(item, test, subtoken, frames.slice(0));

    }.bind(this));

    frame.store(token, events);
    frame.addEvents(events.frame);

    var win = this.getWindow(frame);
    win.addEvents(events.window);
  },

  stopCapture: function() {

    this.stopCaptureFrame(this.getFrame());
  },

  stopCaptureFrame : function(frame, token, frames) {

    token = token  || 'main';
    var events = frame.retrieve(token);

    if (events) {

      frames = frames || [frame];

      frame.removeEvents(events.frame);
      this.getWindow(frame).removeEvents(events.window);

      var subtoken = 'sub' + frames.length;

      this.getFrames(frame).each(function(item) {

        this.stopCaptureFrame(item, subtoken, frames);

      }.bind(this));
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
      tests = this.getTests().slice(key);
    }

    this.testItems(tests, 0, function() {

      sylma.ui.showMessage('All tests passed');
    });
  },

  save : function() {

    var test = this.getTest().save();
  }

});
sylma.stepper.Page = new Class({

  Extends : sylma.stepper.Framed,
  Implements : sylma.stepper.Listed,

  actions : [],

  mode : {
    ready : 1,
    played : 2,
    all : 3
  },

  addStep : function(callback) {

    this.resetSteps(this.mode.ready);
    var current = this.getCurrent();
    this.getParent('main').pauseRecord();

    this.test(function() {

      ++current;
      var step = callback.call(this, current, function() {

        this.getParent('main').resumeRecord();

      }.bind(this));

      step.go();
      this.setCurrent(current);

    }.bind(this), current, true);
  },

  addSnapshot : function() {

    this.addStep(function(key, callback) {

      var result = this.getSteps().add('snapshot', {}, key);
      result.activate(callback);

      return result;
    });
  },

  addEvent : function(e) {

    this.addStep(function(key, callback) {

      var result = this.getSteps().add('event', {event : e}, key);
      if (callback) callback();

      return result;
    });
  },

  addWatcher : function() {

    this.addStep(function(key, callback) {

      var result = this.getSteps().add('watcher', {}, key);
      result.activate(callback);

      return result;
    });
  },

  getSteps : function() {

    return this.getObject('steps')[0];
  },

  record : function(callback) {

    //this.goLast(callback);
  },

  test : function(callback, to, record) {

    this.go(function() {

      sylma.log('test page ' + this.options.url);

      var all = this.getSteps().tmp;

      if (to !== undefined) {

        var current = this.getCurrent();

        if (to < current - 1) {

          this.go(function() {

            this.setCurrent(to + 1);
            this.testNextItem(all.slice(0, to + 1), 0, callback);

          }.bind(this), true);
        }
        else {
/*
 c  t  s  e  f current/to/start/end/futur current

-1 -1  0  0  0 activate 1
-1  0  0  1  1 activate 2
-1  1  0  2  2 activate 3
-1 -1  0  0  0 record
 0 -1  0  0  1 activate 1 after record
 1  0  0  1  1 activate 2 from 1
 1  2  1  1  2 activate 3 from 2
 0  2  0  2  2 activate 3 from 1
 1  0  0  1  1 record from 1
 2  1  0  1  2 record from 2
 */

          this.setCurrent(to + 1);

          if (to < 0) {

            start = 0;
            end = 0;
          }
          else {

            var start = current < 1 ? 0 : current - 1;
            var end = to + 1;
          }
sylma.log(current, to, start, end, this.getCurrent());
        this.testNextItem(all.slice(start, end), 0, callback, record);
        }
      }
      else {

        this.setCurrent(all.length - 1);
        this.testNextItem(all, 0, callback);
      }

    }.bind(this));
  },

  selectStep : function(step) {

    step.setReady(true);
  },

  resetSteps : function(mode) {

    mode = mode || this.mode.played;

    //this.setCurrent();
    this.getSteps().tmp.each(function(item) {

      if (mode & this.mode.ready) item.isReady(false);
      if (mode & this.mode.played) item.isPlayed(false);

    }.bind(this));
  },

  go : function(callback, reload) {

    this.getParent('test').goPage(this);
    var current = this.getWindow().location.pathname;
    var url = this.get('url');
    var diff = current !== url;

    if (reload || diff) {

      this.resetSteps(this.mode.all);
      this.setCurrent();

      this.getWindow().location.href = url;
      this.getFrame().removeEvents().addEvent('load', function() {

        this.select();
        if (callback) callback();

      }.bind(this));

      if (reload && !diff) this.getWindow().location.reload();
    }
    else {

      this.select();
      if (callback) callback();
    }
  },

  goStep: function(step, callback) {

    var key = step.getKey();
//sylma.log(key);
    this.test(function() {

      step.isReady(true);
      if (callback) callback();

    }, key - 1);
  },

  select : function() {
console.log('select');
    this.getNode().addClass('activated');
  },

  unselect : function() {
console.log('unselect');
    this.getNode().removeClass('activated');
    this.resetSteps(this.mode.all);
  },

  testLast : function(items, key, callback, record) {

    var item = items[key];
    var all = this.getSteps().tmp;

    var test = this.getParent('test');
    var lastTest = test.getParent().getObject('test').getLast();

    if (!record && item == all.getLast() && test != lastTest) {

      //this.getParent('test').preparePage(callback);
      this.testItem(items, key);
    }
    else {

      this.testItem(items, key, callback);
    }
  },

  toJSON : function() {

    return {
      '@url' : this.get('url'),
      steps : this.getSteps().tmp
    };
  }
});
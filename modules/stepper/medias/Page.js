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
//var key = current < 0 ? current : current - 1;
    //this.test(function() {

      ++current;

      var result = callback.call(this, current, function() {

        this.getParent('main').resumeRecord();

      }.bind(this));

      result.isReady(true);
      this.setCurrent(current);

    //}.bind(this), current, true);
    return result;
  },

  addSnapshot : function() {

    return this.addStep(function(key, callback) {

      var result = this.getSteps().add('snapshot', {}, key);
      result.activate(callback);

      return result;
    });
  },

  addEvent : function(e) {

    return this.addStep(function(key, callback) {

      var result = this.getSteps().add('event', {event : e}, key);
      if (callback) callback();

      return result;
    });
  },

  addInput: function(e) {

    return this.addStep(function(key, callback) {

      var result = this.getSteps().add('input', {event : e}, key);
      if (callback) callback();

      return result;
    });
  },

  addWatcher : function() {

    return this.addStep(function(key, callback) {

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

      console.log('test page ' + this.options.url);

      var all = this.getSteps().tmp;

      if (to !== undefined) {

        var current = this.getCurrent();
//console.log('current,to', current, to);
        if (to <= current) {

          //this.resetSteps(this.mode.all);

          this.go(function() {

            this.testNextItem(all.slice(0, to + 1), 0, callback);
            this.setCurrent(to);

          }.bind(this), true);
        }
        else {

          this.setCurrent(to);

          if (to < 0) {

            start = 0;
            end = 0;
          }
          else {

            var start = current + 1;
            var end = to + 1;
          }
//console.log('start,end,current', start, end, this.getCurrent());
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

  go : function(callback, reload, reset) {

    this.getParent('test').goPage(this);
    var current = this.getWindow().location.pathname;
    var url = this.get('url');
    var diff = current !== url;

    if (reload || diff) {

      this.resetSteps(this.mode.all);
      this.setCurrent(-1);

      this.getWindow().location.href = url;
      this.getFrame().removeEvents().addEvent('load', function() {

        this.select(callback, reset);

      }.bind(this));

      if (reload && !diff) {

        this.getWindow().location.reload();
      }
    }
    else {

      this.select(callback, reset);
    }
  },

  goStep: function(step, callback) {

    var key = step.getKey();
    this.resetSteps(this.mode.ready);

    this.getParent('main').pauseRecord();

    var select = function() {

      step.isReady(true);
      if (callback) callback();

      this.getParent('main').resumeRecord();
    }.bind(this);

    if (key !== this.getCurrent()) {

      this.test(select, key);
    }
    else {

      select();
    }
  },

  select : function(callback, reset) {

    if (reset) {

      this.setCurrent(-1);
      this.test(callback, -1);
    }
    else if (callback) {

      callback();
    }

    this.toggleActivation(true);
  },

  unselect : function() {

    this.toggleActivation(false);
    this.resetSteps(this.mode.all);
  },

  testLast : function(items, key, callback, record) {

    var item = items[key];
    var all = this.getSteps().tmp;

    var test = this.getParent('test');
    var lastPage = test.getObject('page').getLast();

    if (!record && item == all.getLast() && this != lastPage) {

      this.getParent('test').preparePage(callback);
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
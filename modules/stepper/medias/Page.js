sylma.stepper.Page = new Class({

  Extends : sylma.stepper.Framed,
  Implements : sylma.stepper.Listed,

  actions : [],

  mode : {
    ready : 1,
    played : 2,
    all : 3
  },

  onLoad : function() {

    this.add('rename', {file : this.options.url});

    if (!this.options.url) {

      this.getObject('rename')[0].toggleShow();
      this.updateName();
    }
  },

  addStep : function(callback) {

    this.resetSteps(this.mode.ready);
    var current = this.getCurrent();
    this.getParent('main').pauseRecord();

    ++current;

    var result = callback.call(this, current, function() {

      this.getParent('main').resumeRecord();

    }.bind(this));

    result.isReady(true);
    result.isPlayed(true);

    this.setCurrent(current);

    return result;
  },

  addSnapshot : function() {

    return this.addStep(function(key, callback) {

      var result = this.getSteps().add('snapshot', {}, key);
      result.activate(callback);

      return result;
    });
  },

  addEvent : function(e, frames) {

    return this.addStep(function(key, callback) {

      var result = this.getSteps().add('event', {
        event : e,
        frames : frames
      }, key);

      callback && callback();

      return result;
    });
  },

  addInput: function(e, frames) {

    return this.addStep(function(key, callback) {

      var result = this.getSteps().add('input', {
        event : e,
        frames : frames
      }, key);

      callback && callback();

      return result;
    });
  },

  addWatcher : function(options) {

    return this.addStep(function(key, callback) {

      var result = this.getSteps().add('watcher', options, key);

      if (!options) {

        result.activate(callback);
      }
      else {

        callback && callback();
      }

      return result;
    });
  },

  addCall : function() {

    return this.addStep(function(key, callback) {

      var result = this.getSteps().add('call', {}, key);
      callback && callback();

      return result;
    });
  },

  updateDelay : function(val) {

    this.options.delay = val;
  },

  addQuery : function() {

    return this.addStep(function(key, callback) {

      var result = this.getSteps().add('query', {}, key);
      callback && callback();

      return result;
    });
  },

  getSteps : function() {

    return this.getObject('steps')[0];
  },

  getItems : function() {

    return this.getSteps().tmp;
  },

  checkItem : function() {

    return false;
  },

  record : function(callback) {

    //this.goLast(callback);
  },

  test : function(callback, to, record) {

    var location = this.getParent('test').getLocation();
    var url = this.getURL();

    if (url && url !== location) {

      this.addError({type : 'page', message : 'bad path'});
      this.log('Bad path : "' + location + '"');
    }
    else {

      this.log('Test');
      this.testPage(callback, to, record);
    }
  },

  testPage: function(callback, to, record) {

    var current = this.getCurrent();
    var all = this.getSteps().tmp;

    this.getParent('test').goPage(this);
    this.select();

    if (to !== undefined) {

      if (to <= current) {

        this.testNextItem(all.slice(0, to + 1), 0, callback, record);
        this.setCurrent(to);
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

        this.testNextItem(all.slice(start, end), 0, callback, record);
      }
    }
    else {

      this.setCurrent(all.length - 1);
      this.testNextItem(all, 0, callback);
    }
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

  /**
   * function callback
   * bool reload reload page
   */
  go : function(callback, reload) {

    var location = this.getWindow().location;

    this.getParent('test').goPage(this);

    var current = location.pathname + location.search;
    var url = this.getURL();

    var diff = url && current !== url;

    if (!this.errors.length && (reload || diff)) {

      this.resetSteps(this.mode.all);
      this.setCurrent(-1);

      if (url) {

        location.href = url || location.href;
        this.getParent('main').pauseRecord();

        this.getParent('main').preparePage(function() {

          this.select(function() {

            callback && callback();
            this.getParent('main').resumeRecord();

          }.bind(this));

        }.bind(this));

        if (reload && !diff || !url) {

          this.getWindow().location.reload();
        }
      }
      else {

        this.select(callback);
      }
    }
    else {

      this.select(callback);
    }
  },

  goTest : function(callback) {

    this.go(function() {

      this.test(callback);

    }.bind(this), true);
  },

  goStep: function(step, callback) {

    var key = step.getKey();
    this.resetSteps(this.mode.ready);

    this.getParent('main').pauseRecord();

    var select = function() {

      step.isReady(true);
      callback && callback();

      this.getParent('main').resumeRecord();

    }.bind(this);

    this.isgo = true;
    var reload = !this.isActive() || key <= this.getCurrent();

    if (key !== this.getCurrent()) {

      this.go(function() {

        this.test(select, key);

      }.bind(this), reload);
    }
    else {

      select();
    }
  },

  getURL : function() {

    var parser = this.parseVariables(this.get('url'));

    if (parser.variables) {

      this.getNode('name').set('href', parser.content);
    }
    else if (!parser.content) {

      this.getNode('name').set('href', this.getWindow().location.href);
    }

    return parser.content;
  },

  goURL : function() {


    var location = this.getWindow().location;
    location.href = this.getURL();
  },

  updateName: function() {

    var result = this.options.url;

    this.getNode('name').set({
      html : result ? result : '[any]',
      href : result ? result : '#'
    });
  },

  setFile : function(val, update) {

    this.options.url = val;

    if (update === undefined || update) {

      this.updateName();
    }
  },

  select : function(callback, reset) {

    this.getParent('test').updateFrameSize();

    if (reset) {

      this.setCurrent(-1);
      this.go(function() {

        this.toggleActivation(true);
        this.test(callback, -1);

      }.bind(this), true);
    }
    else {

      callback && callback();
      this.toggleActivation(true);
    }
  },

  unselect : function() {

    this.setCurrent(-1);
    this.toggleActivation(false);
    this.resetSteps(this.mode.all);
  },

  testItems : function(items, key, callback, record) {

    key = key || 0;

    var item = items[key];

    item.hasError(false);
    item.isReady(false);

    item.test(function() {

      item.isPlayed(true);
      this.testNextItem(items, key + 1, callback, record);

    }.bind(this));
  },

  testLast : function(items, key, callback, record) {

    var item = items[key];
    var all = this.getSteps().tmp;

    var test = this.getParent('test');
    var lastPage = test.getObject('page').getLast();

    if (!this.isgo && !record && item == all.getLast() && this != lastPage) {

      if (item.getAlias() === 'event') {

        this.getParent('main').preparePage(callback);
        this.testItem(items, key);
      }
      else {

        this.testItem(items, key, function() {

          test.getObject('page')[this.getKey() + 1].go(callback, true);

        }.bind(this));
      }
    }
    else {

      this.isgo = false;
      this.testItem(items, key, callback);
    }
  },

  toJSON : function() {

    var result = {
      '@url' : this.get('url'),
      steps : this.getSteps().tmp
    };

    return result;
  },

  asToken : function() {

    return 'page(' + this.getKey() + ') : ' + this.options.url;
  }
});
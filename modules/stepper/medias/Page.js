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

    if (!this.options.url) {

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

  addEvent : function(e) {

    return this.addStep(function(key, callback) {

      var result = this.getSteps().add('event', {event : e}, key);
      callback && callback();

      return result;
    });
  },

  addInput: function(e) {

    return this.addStep(function(key, callback) {

      var result = this.getSteps().add('input', {event : e}, key);
      callback && callback();

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

  addCall : function() {

    return this.addStep(function(key, callback) {

      var result = this.getSteps().add('call', {}, key);
      callback && callback();

      return result;
    });
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

  record : function(callback) {

    //this.goLast(callback);
  },

  test : function(callback, to, record) {

    var current = this.getCurrent();

    this.go(function() {

      console.log('test page ' + this.options.url);

      var all = this.getSteps().tmp;

      if (to !== undefined) {

//console.log('current,to', current, to);
        if (to <= current) {

          //this.resetSteps(this.mode.all);

          //this.go(function() {

            this.testNextItem(all.slice(0, to + 1), 0, callback, record);
            this.setCurrent(to);

          //}.bind(this), true);
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

    }.bind(this), to <= current);
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

    var location = this.getWindow().location;

    this.getParent('test').goPage(this);

    var current = location.pathname + location.search;
    var url = this.getUrl();
//console.log('mypath', current, this.getWindow().location);
    var diff = current !== url;

    if ((url || reload) && !this.hasError() && (reload || diff)) {

      this.resetSteps(this.mode.all);
      this.setCurrent(-1);
//console.log(url, this.hasError(), reload, diff);
      location.href = url || location.href;
      this.getParent('main').pauseRecord();

      this.getParent('main').preparePage(function() {

        this.select(function() {

          callback && callback();
          this.getParent('main').resumeRecord();

        }.bind(this), reset);

      }.bind(this));

      if (reload && !diff || !url) {

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
      callback && callback();

      this.getParent('main').resumeRecord();

    }.bind(this);

    this.isgo = true;

    if (key !== this.getCurrent()) {

      this.test(select, key);
    }
    else {

      select();
    }
  },

  getUrl : function() {

    return this.parseVariables(this.get('url'), function(val, vars) {

      if (vars) {

        this.getNode('name').set('href', val);
      }
      else if (!val) {

        this.getNode('name').set('href', this.getWindow().location.href);
      }

    }.bind(this));
  },

  editName : function(update) {

    update = update === undefined ? true : false;

    var result = window.prompt('Please choose a page path', this.options.url || '');

    if (result !== null) {

      this.options.url = result;

      if (update) {

        this.updateName();
      }
    }
  },

  updateName: function() {

    var result = this.options.url;

    this.getNode('name').set({
      html : result ? result : '[any]',
      href : result ? result : '#'
    });
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

    this.setCurrent(-1);
    this.toggleActivation(false);
    this.resetSteps(this.mode.all);
  },

  testItem : function(items, key, callback, record) {

    var item = items[key];

    item.hasError(false);
    item.isPlayed(true);
    item.isReady(false);

    this.testItems(items, key, callback, record);
  },

  testLast : function(items, key, callback, record) {

    var item = items[key];
    var all = this.getSteps().tmp;

    var test = this.getParent('test');
    var lastPage = test.getObject('page').getLast();

    if (!this.isgo && !record && item == all.getLast() && this != lastPage) {

      this.getParent('main').preparePage(callback);
      this.testItem(items, key);
    }
    else {

      this.isgo = false;
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

sylma.stepper.Test = new Class({

  Extends : sylma.stepper.Framed,
  Implements : sylma.stepper.Listed,

  currentKey : undefined,

  onReady : function() {

    if (!this.options.file) {

      this.editName(this.getParent('main').get('directory') + '/', false);
    }
  },

  editName : function(path, update) {

    update = update === undefined ? true : false;

    var result = window.prompt('Please choose a file name', path);

    if (result) {

      this.options.file = result;

      if (update) {

        this.getNode('name').set('html', result);
      }
    }
  },

  initPages : function(callback) {

    if (!this.loaded) {

      this.loaded = true;
      this.getParent('main').loadTest(this.get('file'), function(response) {

        Object.each(response.content.page, function(item) {

          this.add('page', item);

        }.bind(this));

        callback && callback();

      }.bind(this));
    }
    else {

      callback && callback();
    }
  },

  getSample : function() {

    return {
      name : 'html',
      namespace : 'http://www.w3.org/1999/xhtml',
      position : {
        x : 0,
        y : 0
      },
      size : {
        x : 0,
        y : 0
      },
      attributes : {

      },
      children : [
        {
          name : 'body',
          namespace : 'http://www.w3.org/1999/xhtml',
          position : {

          }
        }
      ]
    };
  },

  getPages : function() {

    return this.getObject('page', false);
  },

  getPage : function() {

    var pages = this.getPages();
    //var length = pages.length;

    //if (this.getCurrent() >= length) this.setCurrent(length - 1);

    return pages && pages[this.getCurrent()];
  },

  addPage : function() {

    var result = this.add('page', {
      url : this.getWindow().location.pathname
    });

    result.go();

    return result;
  },

  goPage : function(page) {

    var key = page.getKey();

    if (key !== this.getCurrent()) {

      this.getPages().each(function(item, sub) {

        if (sub !== key) item.unselect();
      });

      this.setCurrent(key);
    }
  },

  toggleSelect : function(val, callback) {

    var el = this.getNode('pages');

    if (this.toggleShow(el, val)) {

      if (!this.options.nofile) {

        this.initPages(callback);
      }

      this.go();
    }

    this.toggleActivation(val);
  },

  go : function() {

    this.getParent('main').goTest(this);
  },

  record : function(callback) {

    this.toggleSelect(true);

    if (!this.getPage()) {

      var page = this.addPage();
      page.addSnapshot();
    }
    else {

      this.getPage().go(callback);
    }

    this.getPage().record();
  },

  preparePage : function(callback) {

    var frame = this.getFrame();

    this.events.test = function() {

      frame.removeEvent('load', this.events.test);
      if (callback) callback();

    }.bind(this);

    frame.removeEvent('load', this.events.test);
    frame.addEvent('load', this.events.test)
  },

  test : function(callback) {

    //this.setCurrent();

    this.log('Test');

    this.toggleSelect(true, function() {

      this.testItems(this.getPages(), 0, callback);

    }.bind(this));
  },

  save : function() {

    var content = JSON.stringify(this);
//console.log(test); return;
    this.send(this.getParent('main').get('save'), {
      file : this.get('file'),
      test : content
    });
  },

  toJSON : function() {

    return {test : {
      '#page' : this.getPages()
    }};
  }

});

sylma.stepper.Test = new Class({

  Extends : sylma.stepper.Framed,
  Implements : sylma.stepper.Listed,

  loaded : false,
  loading: false,

  onLoad : function() {

    this.add('rename', {file : this.options.file});

    if (!this.options.file) {

      this.getObject('rename')[0].toggleShow();
    }
  },

  setFile: function(val, update) {

    this.options.file = val;

    if (update === undefined || update) {

      this.getNode('name').set('html', val);
    }
  },

  initPages : function(callback) {

    if (!this.loaded && !this.loading) {

      this.loading = true;

      this.getParent('main').loadTest(this.get('file'), function(response) {

        if (!response.error) {

          this.loaded = true;
        }

        this.loading = false;

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

    return this.getObject('page', false) || [];
  },

  getPage : function() {

    var pages = this.getPages();
    //var length = pages.length;

    //if (this.getCurrent() >= length) this.setCurrent(length - 1);

    return pages && pages[this.getCurrent()];
  },

  addPage : function() {

    var result = this.add('page', {
      url : this.getLocation()
    }, this.getCurrent() + 1);

    result.go(function() {

      result.addSnapshot();
    });

    return result;
  },

  getLocation : function() {

    var location = this.getWindow().location;

    return location.pathname + location.search;
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

    this.toggleActivation(val);

    if (this.toggleShow(el, val)) {

      if (!this.options.nofile) {

        this.initPages(callback);
      }

      this.go();
    }
    else {

      this.getPages().each(function(item) {

        item.unselect();
      });
    }
  },

  go : function() {

    this.getParent('main').goTest(this);
  },

  record : function(callback) {

    this.toggleSelect(true);

    if (!this.getPage()) {

      var page = this.addPage();
      //page.addSnapshot();
    }
    else {

      this.getPage().go(callback);
    }

    //this.getPage().record();
  },

  test : function(callback) {

    //this.setCurrent();

    this.log('Test');

    this.toggleSelect(true, function() {

      var pages = this.getPages();

      pages[0].test(function() {

        this.testNextItem(pages, 1, callback);

      }.bind(this), undefined, undefined, true);

    }.bind(this));
  },

  testFrom: function() {

    this.getParent('main').test(this.getKey());
  },

  save : function() {

    var content = JSON.stringify(this);
//console.log(test); return;
    this.send(this.getParent('main').get('save'), {
      dir : this.getParent('main').get('directory'),
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

sylma.stepper.Test = new Class({

  Extends : sylma.stepper.Framed,
  Implements : sylma.stepper.Listed,

  currentKey : undefined,

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

    return this.getObject('page');
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

      this.getPages().each(function(item) {
        item.unselect();
      });

      this.setCurrent(key);
    }
  },

  record : function() {

    var callback = this.prepareRecord.bind(this);

    if (!this.getPage()) {

      var page = this.addPage();
      page.addSnapshot(callback);
    }
    else {

      this.getPage().go();
      callback();
    }

    this.getPage().record();
  },

  resetFrame : function() {

    this.getParent('main').resetFrame();
  },

  prepareRecord : function() {

    var win = this.getWindow();
    var frame = this.getFrame();

    win.removeEvents();

    win.addEvent('click', function(e) {

      this.getPage().addEvent(e);

    }.bind(this));

    frame.removeEvents();

    frame.addEvent('load', function() {

      this.addPage();
      this.getPage().addSnapshot(function() {

        this.record();

      }.bind(this));

    }.bind(this));
  },

  preparePage : function(callback) {

    this.getFrame().addEvent('load', function() {

      this.resetFrame();
      if (callback) callback();

    }.bind(this));
  },

  test : function(callback) {

    this.setCurrent();

    this.testItems(this.getPages(), 0, callback);
  },

  toJSON : function() {

    return {test : {
      '#page' : this.getPages()
    }};
  }

});
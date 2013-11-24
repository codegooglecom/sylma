
sylma.stepper.Test = new Class({

  Extends : sylma.stepper.Framed,
  Implements : sylma.stepper.Listed,

  currentKey : undefined,
  events : {},

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

    if (!this.getPage()) {

      var page = this.addPage();
      page.addSnapshot();
    }
    else {

      this.getPage().go();
    }

    this.getPage().record();
  },

  startLoad : function() {

  },

  startCapture: function() {
sylma.log('start record');
    this.events = {

      window : function(e) {

        this.getPage().addEvent(e);

      }.bind(this),
      frame : function() {

        this.addPage();
        this.getPage().addSnapshot();

      }.bind(this)
    };

    this.getFrame().addEvent('load', this.events.frame);
    this.getWindow().addEvent('click', this.events.window);
  },

  stopCapture: function() {
sylma.log('stop record');
    this.getFrame().removeEvent('load', this.events.frame);
    this.getWindow().removeEvent('click', this.events.window);
  },

  preparePage : function(callback) {

    var frame = this.getFrame();

    this.events.test = function() {

      frame.removeEvent('load', this.events.test);
      if (callback) callback();

    }.bind(this);

    frame.addEvent('load', this.events.test)
  },

  test : function(callback) {

    //this.setCurrent();

    this.testItems(this.getPages(), 0, callback);
  },

  toJSON : function() {

    return {test : {
      '#page' : this.getPages()
    }};
  }

});
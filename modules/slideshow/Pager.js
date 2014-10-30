
sylma.slideshow.PagerProps = {

  Extends : sylma.slideshow.ContainerProps,

  prepareContainer : function() {

    var result = this.parent();

    if (this.length) {

      this.preparePager();
      this.updateSize();
    }

    return result;
  },

  goSlide: function(key, notransition, speed) {

    this.parent(key, notransition, speed);
    this.updatePage();
  },

  getPager: function() {

    return this.getNode('pages');
  },

  preparePager: function() {

    var container = this.getPager();
    var dots = [];

    if (container) {

      container.empty();

      this.all.each(function(slide, key) {

        var dot = this.createPage(new Element('span'), key);
        container.grab(dot);

        dots.push(dot);

      }.bind(this));

      this.pages = dots;

      this.showPager();
    }
  },

  createPage: function(dummy, key) {

    var handler = this;

    var result = new Element('a', {
      href : 'javascript:void(0)',
      events : {
        click : function() {

          handler.goPage(key);
          handler.resetLoop();
        }
      }
    });

    result.grab(dummy);

    return result;
  },

  showPager: function() {

    var dots = this.pages;
    var current = 0;
    var length = this.all.length;

    (function() {

      var loop = function() {

        dots[current].addClass('visible');
        current++;

        if (current === length) {

          this.updatePage();
          window.clearInterval(loop);
        }

      }.periodical(50, this);

    }.delay(150, this));
  },

  updatePage : function() {

    var pager = this.getPager();
    var key = this.current;
    var node = pager.getChildren()[key >= this.length ? key - this.length : key];

    pager.getChildren().removeClass('active');

    if (node) node.addClass('active');
  },

  goPage : function(key) {

    var target;

    if (this.current > this.length - 1) {

      if (key !== this.current - this.length) {

        target = key + this.length;
      }
    }
    else {

      if (key !== this.current) {

        target = key;
      }
    }

    if (target !== undefined) {

      this.goSlide(target);
    }
  },
};

sylma.slideshow.Pager = new Class(sylma.slideshow.PagerProps);
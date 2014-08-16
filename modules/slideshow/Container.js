
sylma.slideshow = sylma.slideshow || {};

sylma.slideshow.ContainerProps = {

  Extends : sylma.ui.Loader,
  current : 0,
  width : 0,
  period : 5000,
  infos : false,
  lastWidth : 0,
  length : 0,

  /**
   * @type sylma.device.Browser
   */
  device : null,

  onLoad : function() {

    this.device = this.getParent('main').getDevice();

    if (this.isMobile()) {

      this.device.setupScroll();
    }

    this.prepareContainer();

    this.getCollection()[this.current].prepare();

    this.prepareMobile();

    this.startLoading();

    this.hideInfos();

    if (this.length > 1) {

      this.startLoop();
    }
  },

  prepareContainer: function() {

    this.updateWidth();

    var node = this.getContainer();

    this.getCollection().each(function(item) {

      node.grab(item.clone());

    }.bind(this));

    this.length = this.tmp.length;

    this.all = this.tmp;
    this.tmp = this.tmp.concat(this.tmp);

    this.preparePager();
    this.updateSize();
  },

  scrollTop: function() {

    if (!this.device.scrollTop() && Browser.safari) {

      $('body').setStyle('height', '100%');
    }
  },

  toggleInfos : function() {

    this.getNode('infos').toggleClass('active');
    this.getNode('title').toggleClass('active');

    if (this.infos) this.hideInfos();
    else this.showInfos();

    this.infos = !this.infos;
  },

  showInfos : function() {

    var infos = this.getNode('infos');
    infos.setStyle('margin-top', 0);
  },

  hideInfos : function() {

    var infos = this.getNode('infos');

    if (infos) {

      infos.setStyle('margin-top', - infos.getStyle('height').toInt());
    }
  },

  touchStart : function(e) {

    var mobile = this.device;

    this.prepareSlide(this.getPrevious(true));
    this.prepareSlide(this.getNext(true));

    this.stopLoop();
    this.useTransition(false);
    this.swipe = {

      position : mobile.getPosition(e).x,
      margin : this.getMargin(),
      current : this.current
    };

    e.preventDefault();
  },

  touchEnd : function(e) {

    if (this.swipe) {

      this.current = Math.round(-this.swipe.current / this.width);
      var length = this.getCollection().length;

      if (this.current >= length) this.current = length - 1;

      this.useTransition(true);

      this.prepareSlide(this.getPrevious());
      this.prepareSlide(this.getNext());

      this.goSlide(this.current, false, 'fast');
      this.startLoop();

      this.swipe = null;
    }
  },

  touchMove : function(e) {

    var mobile = this.device;

    if (this.swipe) {

      var position = mobile.getPosition(e).x;
      this.swipe.current = this.swipe.margin - (this.swipe.position - position);
      this.getContainer().setStyle('margin-left', this.swipe.current);

      e.preventDefault();
    }
  },

  prepareMobile : function() {

    var mobile = this.device;

    this.events = {

      touchstart : this.touchStart.bind(this),
      touchend : this.touchEnd.bind(this),
      touchmove : this.touchMove.bind(this)
    };

    $(window).addEvent('resize', this.updateSize.bind(this));

    Object.each(mobile.parseEvents(this.events), function(event, key) {

      this.getContainer().addListener(key, event);

    }.bind(this));

  },

  /**
   * @todo not compatible with ui.Container
   */
  updateContent: function(response, form) {

    //this.stopLoop();

    var result = sylma.ui.parseMessages(response);
    var nodes = sylma.ui.importNode(result.content);
    var container = this.getContainer();

    var props = this.importResponse(result, this, true);

    this.tmp = [];
    this.all = [];

    sylma.ui.addEventTransition(container, function(e) {

      container.empty();
      container.adopt(nodes);

      if (props.objects) this.initObjects(props.objects);
      this.onWindowLoad();

      container.removeClass('destroy');

      this.current = 0;
      this.lastWidth = 0;

      this.prepareContainer();
      this.startLoop();

      this.stopLoading();
      form.hideMask();

    }.bind(this), undefined, true);

    container.addClass('destroy');
  },

  startLoading : function() {

    this.stopLoop();
    this.parent();
  },

  getDir : function(val) {

    return val ? 'left' : 'right';
  },

  getImagePath: function(path, size) {

    size = size || 'large';

    return this.get('directory') + '/' + path + '?format=' + size;
  },

  stopLoop : function() {

    window.clearInterval(this.loop);
  },

  startLoop: function() {

    this.stopLoop();

    this.prepareSlide(this.getPrevious());
    this.prepareSlide(this.getNext());

    this.loop = function() {

      this.updateSpeed();
      this.goNext();

    }.periodical(this.period, this);
  },

  resetLoop: function() {

    this.stopLoop();
    this.startLoop();
  },

  getContainer: function() {

    return this.getNode('container');
  },

  updateWidth: function() {

    this.width = this.getNode().getParent().getStyle('width').toInt();
  },

  updateSize : function() {

    var pageWidth = $('body').offsetWidth;

    if (this.lastWidth !== pageWidth) {

      this.updateSizeConfirm();
    }

    this.lastWidth = pageWidth;
  },

  updateSizeConfirm : function() {

    if (this.isMobile()) {

      this.scrollTop();
    }

    this.updateWidth();

    this.getContainer().setStyles({
      width : this.getCollection().length * this.width
    });

    this.updateSlides();
    this.goSlide(this.current, true);
  },

  getHeight: function() {

    var result = this.getNode().getStyle('height').toInt();

    return result < 100 ? 0 : result;
  },

  updateSlides : function() {

    this.getCollection().each(function(item) {

      item.setWidth(this.width);

    }.bind(this));
  },

  getCollection: function() {

    //return this.getObject('container').tmp;
    return this.tmp;
  },

  prepareSlide: function(key) {

    this.getCollection()[key].prepare();
  },

  useTransition: function(val) {

    var node = this.getContainer();

    if (val) node.offsetHeight;
    node.toggleClass('notransition', !val);
  },

  getNext : function(update) {

    var result;

    if (this.current === this.tmp.length - 1) {

      if (update) {

        this.updateSlide(this.getMargin() + this.getSemiWidth(), true);
      }

      result = this.length;
    }
    else {

      result = this.current + 1;
    }

    return result;
  },

  getPrevious : function(update) {

    var result;

    if (this.current === 0) {

      if (update) {

        this.updateSlide(this.getMargin() - this.getSemiWidth(), true);
      }

      result = this.length - 1;
    }
    else {

      result = this.current - 1;
    }

    return result;
  },

  getSemiWidth: function() {

    return this.width * (this.tmp.length / 2);
  },

  getMargin: function() {

    //return this.getContainer().getStyle('margin-left').toInt();
    return this.getContainer().getComputedStyle('margin-left').toInt();
  },

  goNext: function(speed) {

    this.current = this.getNext(true);

    this.prepareSlide(this.getNext());
    this.goSlide(this.current, false, speed);
  },

  goPrevious: function(speed) {

    this.current = this.getPrevious(true);

    this.prepareSlide(this.getPrevious());
    this.goSlide(this.current, false, speed);
  },

  updateSpeed: function(speed) {

    var container = this.getContainer();
    var property = this.device.getProperty('transition-duration');

    switch (speed) {

      case 'normal' : speed = '0.5s'; break;
      case 'fast' : speed = '0.2s'; break;
    }

    container.setStyle(property, speed);
  },

  goSlide: function(key, notransition, speed) {

    this.current = key;
    this.prepareSlide(key);

    if (speed) {

      this.updateSpeed(speed);
    }

    this.updateRelated();
    this.updateSlide(-key * this.width, notransition);
    this.updatePage();
  },

  updateRelated : function() {

    var related = this.getNode('related');

    if (related) {

      var item = this.getCollection()[this.current];
      var token = 'portfolio';
      var old = related.retrieve(token);

      if (old) {

        this.hide(old, function() {

          old.dispose();
        });
      }

      var content = new Element('span', {
        html : item.get('name'),
        'class' : 'sylma-hidder'
      });

      related.set('href', this.get('project') + item.get('id'));
      related.grab(content);

      (function() {

        this.show(content);

      }).bind(this).delay(1);

      related.store(token, content);
    }
  },

  updateSlide : function(margin, notransition) {

    if (notransition) this.useTransition(false);
    this.getContainer().setStyle('margin-left', margin);
    if (notransition) this.useTransition(true);
  },

  getPager: function() {

    return this.getNode('pages');
  },

  preparePager: function() {

    var container = this.getPager();
    var dots = [];

    container.empty();

    this.all.each(function(slide, key) {

      var dot = this.createPage(new Element('span'), key);
      container.grab(dot);

      dots.push(dot);

    }.bind(this));

    this.pages = dots;

    this.showPager();
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
  }
};

sylma.slideshow.Container = new Class(sylma.slideshow.ContainerProps);
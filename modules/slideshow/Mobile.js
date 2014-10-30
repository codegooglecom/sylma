
sylma.slideshow.MobileProps = {

  Extends : sylma.slideshow.PagerProps,

  /**
   * @type sylma.device.Browser
   */
  device : null,

  onLoad : function() {

    var main = this.getParent('main');
    this.device = main.getDevice();

    if (this.isMobile()) {

      this.device.setupScroll();
    }

    if (this.prepareContainer()) {

      this.getCollection()[this.current].prepare();
      this.prepareMobile();
      this.startLoading();
      this.hideInfos();

      if (this.length > 1) {

        this.startLoop();
      }
    }
  },

  isMobile: function() {

    var main = this.getParent('main');

    return main && main.isMobile();
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

  updateSizeConfirm : function() {

    if (this.isMobile()) {

      this.scrollTop();
    }

    this.parent();
  },

  scrollTop: function() {

    if (!this.device.scrollTop() && Browser.safari) {

      $('body').setStyle('height', '100%');
    }
  },

};

sylma.slideshow.Mobile = new Class(sylma.slideshow.MobileProps);
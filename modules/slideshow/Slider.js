
sylma.slideshow.SliderProps = {

  Extends : sylma.slideshow.Pager,

  prepareContainer: function() {

    this.updateWidth();

    var node = this.getContainer();

    this.getCollection().each(function(item) {

      node.grab(item.clone());

    }.bind(this));

    this.length = this.tmp.length;

    this.all = this.tmp;
    this.tmp = this.tmp.concat(this.tmp);

    if (this.length) {

      this.updateSize();
    }

    return this.length;
  },

  updateWidth: function() {

    this.width = this.getNode().getParent().getStyle('width').toInt();
  },

  updateSize : function() {

    var pageWidth = $(window.document.body).offsetWidth;

    if (this.lastWidth !== pageWidth) {

      this.updateSizeConfirm();
    }

    this.lastWidth = pageWidth;
  },

  updateSizeConfirm : function() {

    this.updateWidth();

    this.getContainer().setStyles({
      width : this.getCollection().length * this.width
    });

    this.updateSlides();
    this.goSlide(this.current, true);
  },

  updateSlides : function() {

    this.getCollection().each(function(item) {

      item.setWidth(this.width);

    }.bind(this));
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

  updateSlide : function(key, notransition) {

    if (notransition) this.useTransition(false);
    this.getContainer().setStyle('margin-left', -key * this.width);
    if (notransition) this.useTransition(true);
  }

};

sylma.slideshow.Slider = new Class(sylma.slideshow.SliderProps);
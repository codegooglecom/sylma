
sylma.slideshow = sylma.slideshow || {};

/**
 * @required parent 'main'
 */
sylma.slideshow.ContainerProps = {

  Extends : sylma.ui.Loader,

  current : 0,
  width : 0,
  period : 5000,
  infos : false,
  lastWidth : 0,
  length : 0,

  onLoad : function() {

    var main = this.getParent('main');

    this.period = this.options.delay || this.period;

    if (main) {

      this.device = main.getDevice();
    }
    else {

      this.device = new sylma.device.Browser();
    }

    if (this.prepareContainer()) {

      this.getCollection()[this.current].prepare();
      this.startLoading();
      this.hideInfos();
      this.updateSlide(0);

      if (this.length > 1) {

        this.startLoop();
      }
    }
  },

  prepareContainer: function() {

    this.length = this.tmp.length;
    this.all = this.tmp;

    return this.length;
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

  getHeight: function() {

    var result = this.getNode().getStyle('height').toInt();

    return result < 100 ? 0 : result;
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

  getNext : function() {

    var result;

    if (this.current === this.length - 1) {

      result = 0;
    }
    else {

      result = this.current + 1;
    }

    return result;
  },

  getPrevious : function() {

    var result;

    if (this.current === 0) {

      result = this.length - 1;
    }
    else {

      result = this.current - 1;
    }

    return result;
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

    this.updateSlide(key, notransition);
  },

  updateSlide : function(key, notransition) {

    this.all.each(function(slide) {
      slide.getNode().removeClass('visible');
    });

    this.all[key].getNode().addClass('visible');
  }
};

sylma.slideshow.Container = new Class(sylma.slideshow.ContainerProps);
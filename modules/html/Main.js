
sylma.html = sylma.html || {};

sylma.html.Main = new Class({

  Extends : sylma.ui.Container,

  device : null,

  onReady : function() {

    if (sylma.device) {

      this.device = new sylma.device.Browser();
      this.device.resetLinks();
    }
  },

  getDevice : function () {

    return this.device;
  },

  isMobile : function() {

    return this.get('mobile');
  }
});
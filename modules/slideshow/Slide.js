
sylma.slideshow.Slide = new Class({

  Extends : sylma.ui.Container,

  prepare: function() {
//console.log(this.get('path'));
    if (!this.prepared) {

      this.prepared = true;
      this.loadImage(this.getNodes(), this.getImagePath(), function() {

        this.getParent('handler').stopLoading();

      }.bind(this));
    }
  },

  loadImage : function(nodes, src, callback) {

    new Element('img', {
      src : src,
      events : {
        load : function() {

          this.readyImage(nodes, src);
          callback && callback();

        }.bind(this)
      }
    });
  },

  readyImage : function(nodes, src) {

    nodes.setStyle('background-image', "url('" + src + "')");

    (function() {

      nodes.addClass('ready');

    }).bind(this).delay(150);
  },

  setWidth : function(val) {

    this.getNodes().setStyle('width', val);
  },

  getNodes: function() {

    return $$(this.getNode(), this.cloned);
  },

  clone: function(val) {

    this.cloned = this.getNode().cloneNode(true);
    return this.cloned;
  },

  getImagePath : function(size) {

    return this.getParent('handler').getImagePath(this.get('path'), size);
  },

  toggleFullscreen : function(toggle) {

    
  }

});
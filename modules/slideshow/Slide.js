
sylma.slideshow.Slide = new Class({

  Extends : sylma.ui.Container,

  prepare: function() {
//console.log(this.get('path'));
    if (!this.prepared) {

      this.prepared = true;

      new Element('img', {
        src : this.getImagePath(),
        events : {
          load : function() {

            this.updateNode();
            this.getParent('handler').stopLoading();

          }.bind(this)
        }
      });
    }
  },

  updateNode : function(nodes, size) {

    nodes = nodes || this.getNodes();

    nodes.setStyle('background-image', "url('" + this.getImagePath(size) + "')");

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
  }

});
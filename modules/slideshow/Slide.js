
sylma.slideshow.Slide = new Class({

  Extends : sylma.ui.Container,

  prepare: function() {
//console.log(this.get('path'));
    if (!this.prepared) {

      this.prepared = true;
      var path = this.getDirectory() + '/' + this.get('path') + '?size=large';

      new Element('img', {
        src : path,
        events : {
          load : function(e) {

            this.getParent('handler').stopLoading();
            this.getNodes().setStyle('background-image', "url('" + path + "')");

            (function() {

              this.getNodes().addClass('ready');

            }).bind(this).delay(150);

          }.bind(this)
        }
      });
    }
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

  getDirectory : function() {

    return this.getParent('handler').get('directory');
  }

});
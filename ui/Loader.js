
sylma.ui.Loader = new Class({

  Extends : sylma.ui.Container,

  loaderNode : null,

  options : {
    loaderPosition : true,
  },

  startLoading : function() {

    var node = this.loaderNode;

    if (!node) {

      this.loaderNode = node = this.buildLoader();
    }

    this.getNode().grab(node, 'before');
    var el = this.getNode();

    var x = 0;
    var y = 0;

    if (this.options.loaderPosition !== '0') {

      x = el.getPosition().x;
      y = el.getPosition().y;
    }

    node.setStyles({
      width : el.getSize().x,
      height : this.getContainer().getSize().y,
      position: 'absolute',
      top : y,
      left : x
    });

    node.addClass('active');

    var mainNode = this.getNode();

    if (mainNode) {

      mainNode.addClass('loading');
    }
  },

  buildLoader : function() {

    return new Element('div', {
      'class' : 'loader'
    });
  },

  getContainer : function() {

    return this.getNode();
  },

  stopLoading: function() {

    var node = this.loaderNode;

    if (node) {
      node.removeClass('active');
      node.setStyles({
        height : 0
      });
    }

    var mainNode = this.getNode();

    if (mainNode) {

      mainNode.removeClass('loading');
    }
  }
});
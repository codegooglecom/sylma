
sylma.ui.Loader = new Class({

  Extends : sylma.ui.Container,

  loaderNode : null,

  startLoading : function() {

    var node = this.loaderNode;

    if (!node) {

      node = new Element('div', {
        'class' : 'loading'
      });

      this.loaderNode = node;
    }

    this.getNode().grab(node, 'before');
    var el = this.getNode();

    node.setStyles({
      width : el.getSize().x,
      height : this.getContainer().getSize().y,
      top : el.getPosition().y,
      left : el.getPosition().x,
      position: 'absolute'
    });

    node.addClass('active');
  },

  getContainer : function() {

    return this.getNode();
  },

  stopLoading: function() {

    var node = this.loaderNode;

    node.removeClass('active');
    node.setStyles({
      height : 0
    });
  },

  isMobile: function() {

    return this.getParent('main').isMobile();
  }
});
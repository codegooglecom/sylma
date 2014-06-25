
sylma.ui.tab.Caller = new Class({

  Extends : sylma.ui.Base,

  onLoad : function() {

    this.prepareNode();
  },

  prepareNode : function() {

    var node;

    switch (this.get('mode')) {

      case 'normal' :

        break;

      case 'inside' :

        node = new Element('a', {
          html : this.getNode().get('html')
        });

        this.getNode().empty().grab(node);
        this.buildLink(node);
        break;

      default :

        node = this.getNode();
        this.buildLink(node);
    }
  },

  buildLink: function(node) {

    var self = this;

    node.set({
      href : '#',
      events : {
        click : function(e) {

          this.blur();
          self.go();

          e.preventDefault();

        }
      }
    });
  },

  go : function(e) {

    if (e) {

      e.preventDefault();
    }

    this.getParent(1).go(this.sylma.key);
  }
});

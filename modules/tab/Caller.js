
sylma.ui.tab.Caller = new Class({

  Extends : sylma.ui.Base,

  options : {
    mode : 'inside'
  },

  initialize : function(props) {

    this.parent(props);

    this.prepareNode();
  },

  prepareNode : function() {

    var node;

    if (this.get('mode') == 'inside') {

      node = new Element('a', {
        html : this.getNode().get('html')
      });

      this.getNode().empty().grab(node);
    }
    else {

      node = this.getNode();
    }

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

  go : function() {

    this.getParent(1).go(this.sylma.key);
  }
});

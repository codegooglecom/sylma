
sylma.ui.tab.Container = new Class({

  Extends : sylma.ui.Container,
  width : 0,

  initialize : function(props) {

    this.parent(props);
    this.setWidth(this.getNode().getParent().getSize().x);
    this.getNode().getParent().setStyles({
      position: 'relative',
      overflow : 'hidden'
    });
  },

  setWidth : function(val) {

    this.width = val;
    this.prepareNode();
  },

  getWidth : function() {

    return this.width;
  },

  prepareNode : function() {

    this.getNode().setStyles({
      width : this.width * this.tmp.length,
      //overflow : 'hidden'
    }).addClass('sylma-tab-container');
  },

  go : function(index) {

    this.getNode().setStyle('marginLeft', - this.width * index);
  }

});

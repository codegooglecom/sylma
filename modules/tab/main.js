
sylma.ui.tab = {};

sylma.ui.tab.Main = new Class({

  Extends : sylma.crud.Form,
  tabs : {},
  width : 0,
  current : 0,

  initialize : function(props) {

    this.parent(props);
    this.build(this.getObject('container').tmp);
  },

  prepareNode : function() {

    this.getNode().addClass('sylma-tabs');
  },

  build : function(tabs) {

    this.prepareNode();
    this.tabs = tabs;

    //this.width = this.getNode().getSize().x;
    this.width = this.getObject('container').getWidth();

    for (var i = 0; i < this.tabs.length; i++) {

      this.tabs[i].setWidth(this.width);
    }

    this.go(0);
  },

  parseMessage : function(msg) {

    var alias = msg.arguments.alias;
    this.highlightTab(alias);
  },

  highlightTab : function(alias) {

    for (var i in this.tabs) {

      if (this.tabs[i].highlight(alias)) {

        break;
      }
    }
  },

  getTab : function(index) {

    return this.tabs[index];
  },

  go : function(index) {

    this.current = index;

    this.getObject('container').go(index);
    this.getObject('head').downlightAll();

    this.getObject('head').getCaller(index).highlight();
    this.getTab(index).show();
  },

  goNext : function() {

    if (this.current < this.tabs.length - 1) {

      this.go(this.current + 1);
    }
  },

  goPrevious : function() {

    if (this.current > 0) {

      this.go(this.current - 1);
    }
  }
});

sylma.ui.tab.Head = new Class({

  Extends : sylma.ui.Base,

  initialize : function(props) {

    this.parent(props);
    this.getNode().addClass('sylma-tab-head');
  },

  downlightAll : function() {

    var len = this.tmp.length;

    for (var i = 0; i < len; i++) {

      this.tmp[i].downlight();
    }
  },

  getCaller : function(index) {

    return this.tmp[index];
  }

});

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
        click : function() { this.blur(); self.go(); return false; }
      }
    });
  },

  go : function() {

    this.getParent(1).go(this.parentKey);
  }
});

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

sylma.ui.tab.Tab = new Class({

  Extends : sylma.crud.Group,
  width : 0,

  initialize : function(options) {

    this.parent(options);
    this.getNode().addClass('sylma-tab');
  },

  setWidth : function(val) {

    this.width = val;
    this.prepareNode();
  },

  prepareNode : function() {

    this.getNode().setStyle('width', this.width);
  },

  updateSuccess : function(response) {

    this.parent(response);
    this.prepareNode();
  },

  needUpdate : function() {

    return this.get('path') && !this.getNode().getChildren().length;
  },

  show : function() {

    if (this.needUpdate()) {

      this.update({}, this.get('path'));
    }
  }
});

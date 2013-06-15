
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

    this.getNode().setStyles({
      overflow : 'hidden'
    }).addClass('sylma-tabs');
  },

  build : function(tabs) {

    this.prepareNode();
    this.tabs = tabs;

    this.width = this.getNode().getSize().x;
    this.getObject('container').setWidth(this.width);

    for (var i = 0; i < this.tabs.length; i++) {

      this.tabs[i].setWidth(this.width);
    }

    this.go(0);
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

  initialize : function(props) {

    this.parent(props);

    var content = this.getNode().get('html');
    var self = this;

    this.getNode().empty().grab(new Element('a', {
      href : '#',
      html : content,
      events : {
        click : function() { this.blur(); self.go(); return false; }
      }
    }));
  },

  go : function() {

    this.getParent(1).go(this.parentKey);
  },

  highlight : function() {

    this.getNode().addClass('sylma-tab-current sylma-highlight');
  },

  downlight : function() {

    this.getNode().removeClass('sylma-tab-current sylma-highlight')
  }
});

sylma.ui.tab.Container = new Class({

  Extends : sylma.ui.Container,
  width : 0,

  initialize : function(props) {

    this.parent(props);

  },

  setWidth : function(val) {

    this.width = val;
    this.prepareNode();
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

  Extends : sylma.ui.Container,
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

  show : function() {

    var path = this.get('path');

    if (path && !this.getNode().getChildren().length) {

      this.update({}, path);
    }
  }
});

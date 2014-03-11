
sylma.ui.tab = {};

sylma.ui.tab.Main = new Class({

  Extends : sylma.crud.Form,
  tabs : {},
  width : 0,
  current : 0,
  length : 0,
  first : null,

  initialize : function(props) {

    this.parent(props);
    this.build(this.getObject('container').tmp);
  },

  prepareNode : function() {

    this.getNode().addClass('sylma-tabs');
  },

  build : function(tabs) {

    this.prepareNode();
    //this.tabs = tabs;
    this.length = tabs.length;

    //this.width = this.getNode().getSize().x;
    this.width = this.getObject('container').getWidth();

    for (var i = 0; i < tabs.length; i++) {

      this.tabs[i] = tabs[i]
      this.tabs[i].prepare(this.width, i);
    }

    this.go(0);
  },

  parseMessage : function(msg) {

    var alias = msg.arguments.alias;
    var path = this.parseMessageAlias(alias);

    return this.highlightTab(path.alias, path.sub);
  },

  highlightTab : function(alias, sub) {

    var result;

    for (var i in this.tabs) {

      if (this.tabs[i].highlight(alias, sub)) {

        if (this.first === null || this.first > i) {

          this.first = i;
        }

        break;
      }
    }

    return result;
  },

  submitParse : function(response, args) {

    for (var i in this.tabs) {

      this.tabs[i].resetHighlight();
    }

    this.parent(response, args);

    if (this.first !== null) {

      this.go(this.first);
      this.first = null;
    }
  },

  getTab : function(index) {

    return this.tabs[index];
  },

  getHead : function() {

    return this.getObject('head');
  },

  go : function(index) {

    this.current = parseInt(index);

    this.getObject('container').go(index);
    this.getObject('head').downlightAll();

    this.getObject('head').getCaller(index).highlight();
    this.getTab(index).show();
  },

  goNext : function() {

    if (this.current < this.length - 1) {

      this.go(this.current + 1);
    }
  },

  goPrevious : function() {

    if (this.current > 0) {

      this.go(this.current - 1);
    }
  }
});

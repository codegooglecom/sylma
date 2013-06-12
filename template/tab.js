

sylma.ui.Main = new Class({

  Extends : sylma.crud.Form,
  tabs : {},
  width : 0,
  current : 0,

  initialize : function(props) {

    this.parent(props);
    this.build(this.tmp);
  },

  build : function(tabs) {

    this.tabs = tabs;

    this.width = this.getNode().getSize().x;

    this.getNode('container').setStyles({
      width : this.width * this.tmp.length,
      overflow : 'hidden'
    });

    var self = this;

    this.getNode().getElements('ol > li').each(function(el, index) {

      var name = el.get('html');

      el.erase('html');

      var link = new Element('a', {
          href : '#',
          html : name
      });

      link.addEvent('click', function() { self.go(index); return false; });
      el.grab(link);
    });

    for (var i = 0; i < this.tabs.length; i++) {

      this.tmp[i].setWidth(this.width);
    }
  },

  go : function(index) {

    this.current = index;

    this.getNode('container').setStyle('marginLeft', - this.width * index);
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

sylma.ui.Tab = new Class({

  Extends : sylma.ui.Base,

  initialize : function(options) {

    this.parent(options);
  },

  setWidth : function(val) {

    this.getNode().setStyle('width', val);
  }
});
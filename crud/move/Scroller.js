
sylma.crud = sylma.crud || {};
sylma.crud.move = sylma.crud.move || {};

sylma.crud.move.Scroller = new Class({

  Extends : sylma.ui.Container,

  onLoad : function() {

    document.body.grab(this.getNode());
  },

  setFile : function(file) {

    this.file = file;
  },

  scroll : function(dir,e) {

    var mouse = e.page.y;
    dir = dir * 7;

    var max = window.getScrollSize().y - window.getSize().y;
    var current = window.getScroll();

    this.loop = window.setInterval(function() {

      current.y += dir;
      mouse += dir;

      if (current.y <= max && current.y >= 0) {

        window.scrollTo(current.x, current.y);
        this.file.move(mouse);
      }

    }.bind(this), 10);
  },

  stopScroll : function() {

    window.clearInterval(this.loop);
  }
});
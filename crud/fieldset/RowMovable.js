
sylma.crud.fieldset.RowMovable = new Class({

  Extends : sylma.crud.fieldset.Row,
  mask : null,
  offset : 0,

  buildMask : function(y) {

    var result = new Element('div', {
      styles : {
        display : 'block',
        height : y
      }
    });

    this.mask = result;

    return result;
  },

  drag : function(e) {

    this.getNode('move').addClass('active');

    var node = this.getNode();
    var position = node.getPosition();
    var top = e.page.y;

    this.moved = true;

    this.offset = top - position.y;
    this.buildMask(node.getSize().y);

    var objects = this.getParent().tmp;

    objects.sort(function(a, b) {
      return a.getNode().getPosition().y - b.getNode().getPosition().y;
    });

    this.attachEvents(objects);

    node.setStyles({
      left : position.x,
      width : node.getStyle('width'),
      height : node.getStyle('height')
    });

    this.mask.inject(node, 'after');
    node.addClass('moved');

    this.buildScrollers();

    window.body.grab(node, 'top');

    this.updatePosition(top);
  },

  buildScrollers: function() {

    this.getScroller().setFile(this);
    this.getScroller().show();
  },

  getScroller : function() {

    return this.getParent('fieldset').getObject('scroller');
  },

  attachEvents: function(objects) {

    var node = this.getNode();

    var scroll = this.scroll = {

      parent : node.getParent(),
      length : objects.length - 1,
      height : node.getSize().y,
      current : this.getKey()
    };

    scroll.offset = objects.pick().getNode().getPosition().y + this.offset - scroll.height / 2;

    this.events = {
      mousemove : function(e) {

        this.moveTo(e.page.y);

      }.bind(this),
      mouseup : function(e) {

        this.release();

      }.bind(this)
    };

    window.addEvents(this.events);
  },

  move: function(to) {

    if (this.moved) {

      this.moveTo(to);
    }
  },

  moveTo: function(to) {

    var scroll = this.scroll;

    var length = scroll.length;
    var height = scroll.height;
    var current = scroll.current;

    var children = scroll.parent.getChildren('.field-file');

    this.updatePosition(to);

    var key = Math.round((to - scroll.offset) / height - 0.5);

    key = key > 0 ? key : 0;

    if (key >= length) {

      key = length;

      if (key !== current) {

        this.moveMask(children[key - 1], height, 'after');
      }
    }
    else {

      var target = children[key];

      if (key !== current) {

        this.moveMask(target, height);
      }
    }

    this.scroll.current = key;
  },

  moveMask : function(target, height, dir) {

    var old = this.mask;
    dir = dir || 'before';

    if (target) {

      var mask = this.buildMask();
      mask.inject(target, dir);

      old.set('tween', {
        onComplete : function() {

          old.destroy();
        }
      });

      mask.tween('height', height);
      old.tween('height', 0);
    }
  },

  updatePosition: function(val) {

    this.getNode().setStyle('top', val - this.offset);
  },

  updatePositionInput : function(val) {

    this.getNode('position').set('value', val);
  },

  release: function() {

    this.moved = false;

    this.getScroller().hide();
    this.getNode('move').removeClass('active');

    var node = this.getNode();

    node.replaces(this.mask);

    node.removeClass('moved');
    node.setStyles({
      top : 0,
      left : 0,
      display: null,
      width : null,
      height : null
    });

    this.mask.destroy();

    node.getParent().getChildren().each(function(el, key) {

      var obj = el.retrieve('sylma-object');
      obj.updatePositionInput(key + 1);
    });

    window.removeEvents(this.events);
  }
});
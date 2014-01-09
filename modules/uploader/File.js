
sylma.uploader.File = new Class({

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

  move : function(e) {

    this.getNode('move').addClass('active');

    var node = this.getNode();
    var size = node.getSize();
    var position = node.getPosition();
    var top = e.page.y;

    this.offset = top - position.y;
    this.buildMask(size.y);

    var objects = this.getParent().tmp;

    objects.sort(function(a, b) {
      return a.getNode().getPosition().y - b.getNode().getPosition().y;
    });

    var length = objects.length;

    var parent = node.getParent();
    var first = objects.pick().getNode();
    var height = node.getSize().y;
    var nth = first.getAllPrevious().length;
    var current = node.getAllPrevious().length - nth;

    var offset = first.getPosition().y + this.offset - height / 2;

    this.events = {
      mousemove : function(e) {

        var children = parent.getChildren();

        var pos = e.page.y;
        this.updatePosition(pos);

        var key = Math.round((pos - offset) / height - 0.5);

        key = key > 0 ? key : 0;

        if (key >= length - 1) {

          key = length - 1;

          if (key !== current) {

            this.moveMask(children[nth + key], height, 'after');
          }
        }
        else {

          var target = children[nth + key];

          if (key !== current) {

            if (key !== current + 1) {

              this.moveMask(target, height);
            }
            else {

              this.moveMask(target.getNext(), height);
            }
          }
        }

        current = key;

      }.bind(this),
      mouseup : function(e) {

        this.release();

      }.bind(this)
    };

    window.addEvents(this.events);

    node.setStyles({
      left : position.x,
      width : node.getStyle('width'),
      height : node.getStyle('height')
    });

    this.mask.inject(node, 'after');
    node.addClass('moved');

    window.body.grab(node);

    this.updatePosition(top);
  },

  moveMask : function(target, height, dir) {

    var old = this.mask;
    dir = dir || 'before';
    old.set('tween', {
      onComplete : function() {

        old.destroy();
      }
    });

    this.mask.tween('height', 0);

    var mask = this.buildMask();
    mask.inject(target, dir);
    mask.tween('height', height);
  },

  updatePosition: function(val) {

    this.getNode().setStyle('top', val - this.offset);
  },

  release: function() {

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
    window.removeEvents(this.events);
  }
});

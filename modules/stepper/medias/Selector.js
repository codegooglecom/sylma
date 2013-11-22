sylma.stepper.Selector = new Class({

  Extends : sylma.stepper.Framed,

  mask : null,
  activated : false,
  options : {
    target : null, // dom node
    element : null // string
  },

  onReady : function() {

    var target = this.options.target;

    if (!target) {

      if (!this.options.element) {

        this.activated = true;
      }
    }
    else {

      this.set('element', this.buildPath(target));
    }
  },

  activate : function(callback) {

    this.onSelect = callback;
    this.activated = true;

    var mask = this.mask = new Element('div', {
      id : 'selector'
    });

    var overlay = this.overlay = new Element('div', {
      id : 'overlay',
    });

    this.getFrame().setStyle('z-index', 70);

    document.body.adopt(mask, overlay);
    overlay.tween('opacity', 0.35);

    this.resetWindow();
  },

  resetWindow : function() {

    var win = this.getWindow();
    var self = this;

    win.removeEvents();

    win.addEvents({
      mousemove : function(e) {

        self.selectElement(e.target);

      },
      click : function(e) {

        self.selectElement(e.target);
        self.select(e.target);

        e.preventDefault();
      }
    });
  },

  selectElement : function(target) {

    var el = this.mask;

    el.setPosition(target.getPosition());
    var size = target.getSize();
    el.setStyles({
      width: size.x,
      height: size.y
    });
  },

  select : function(target) {

    this.activated = false;

    //this.set('target', target);
    this.set('element', this.buildPath(target));

    this.getWindow().removeEvents();
    this.mask.remove();

    new Fx.Morph(this.overlay, {
      onComplete : function() {

        this.overlay.remove();
        this.getFrame().setStyle('z-index', 50);

      }.bind(this),
      duration : 500
    }).start({
      opacity : 0
    });

    if (this.onSelect) {

      this.onSelect(target);
    }

    if (this.onSelectAdd) {

      this.onSelectAdd();
      delete this.onSelectAdd;
    }
  },

  getElement : function() {

    //if (!this.options.target) {

      var path = this.options.element;

      if (!path) {

        throw new Error("Cannot get selector's path while no element neither path are defined");
      }

      //this.options.target = this.getWindow().document.body.getElement(path);
      var result = this.getWindow().document.body.getElement(path);
    //}

    return result;
    //return this.options.target;
  },

  getPath : function() {

    var result = this.get('element');

    return result;
  },

  buildPath : function(target) {

    var useID = false;

    var result = $$(target).append(target.getParents()).map(function(el) {

      var result = null;

      if (!useID) {

        if (el.id && !el.id.match(/^sylma/)) {

          result = '#' + el.id;
          useID = true;
        }
        else {

          var name = el.get('tag');
          var previous = '';

          if (el.getSiblings().filter(name).length) {

            var count = el.getAllPrevious().length;
            previous = ':nth-child(' + (count + 1) + ')';
          }

          result = name + previous;
        }
      }

      return result;

    }).clean().reverse().join(' > ');

    //console.log(result);

    return result;
  },

  addTo : function(node) {

    if (this.activated) {

      this.onSelectAdd = function() {

        this.set('element', this.getPath());
        this.addTo(node);

      }.bind(this);
    }
    else {

      this.parent(node);
    }
  },

  toJSON : function() {

    return this.options.element;
  }
});

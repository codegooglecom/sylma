sylma.stepper.Selector = new Class({

  Extends : sylma.stepper.Framed,

  mask : null,
  activated : false,
  masked : false,
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

    var mask = this.getMask();
    mask.fade('in');
    var overlay = this.overlay = new Element('div', {
      id : 'overlay',
    });

    this.getFrame().setStyle('z-index', 70);

    document.body.adopt(overlay);
    overlay.tween('opacity', 0.35);

    this.startCapture();
  },

  getMask : function() {

    var result = this.getParent('main').selectorMask;

    if (!result) {

      var result = this.getParent('main').selectorMask = new Element('div', {
        'class' : 'selector-mask'
      });

      document.body.adopt(result);
    }

    return result;
  },

  startCapture : function() {

    this.getParent('main').pauseRecord();

    this.events = {
      click : function(e) {

        this.selectElement(e.target);
        this.select(e.target);

        e.preventDefault();

      }.bind(this),
      mousemove : function(e) {

        this.selectElement(e.target);

      }.bind(this)
    };

    this.getWindow().addEvents(this.events);
  },

  stopCapture: function() {

    this.getParent('main').resumeRecord();
    this.getWindow().removeEvents(this.events);
  },

  windowMove : function(e) {


  },

  windowClick : function(e) {

  },

  toggleMask : function() {

    var mask = this.getMask();

    if (!this.masked) {

      this.selectElement(this.getElement());
      mask.fade('in');
    }
    else {

      mask.fade('out');
    }

    this.masked = !this.masked;
  },

  selectElement : function(target) {

    var el = this.getMask();

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
    this.updateElement(target);

    this.stopCapture();
    this.getMask().fade('out');

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

  updateElement : function(target) {

    this.set('element', this.buildPath(target));
  },

  changeElement: function(target) {

    if (!target) {

      sylma.ui.showMessage('No element found');
    }
    else {

      this.selectElement(target);
      this.updateElement(target);

      var path = this.get('element');

      this.getNode('display').set({
        html : path,
        title : path
      });
    }
  },

  selectParent : function() {

    var parent = this.getElement().getParent();

    if (parent.get('tag') !== 'body') {

      this.changeElement(parent);
    }
    else {

      sylma.ui.showMessage('Top elements reached');
    }
  },

  selectChild : function() {

    this.changeElement(this.getElement().getFirst());
  },

  selectNext : function() {

    this.changeElement(this.getElement().getNext());
  },

  selectPrevious : function() {

    this.changeElement(this.getElement().getPrevious());
  },

  getElement : function() {

    var path = this.options.element;
    var result = this.getWindow().document.body.getElement(path);

    return result;
  },

  getPath : function() {

    var result = this.get('element');

    return result;
  },

  buildPath : function(target) {

    var useID = false;

    var result = $$([target]).append(target.getParents().slice(0, -2)).map(function(el, key, array) {

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

          if (key === array.length - 1) result = ' > ' + result;
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

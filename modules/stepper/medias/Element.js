sylma.stepper.Element = new Class({

  initialize : function(el, options) {

    this.element = el;
    this.options = options;

    if (options) {

      if (options.children) {

        this.loadChildrenOptions(el, options);
      }
    }
    else {

      this.loadChildren(el);
    }
  },

  setChildren : function(children) {

    this.children = children;
  },

  getChildren : function() {

    return this.children;
  },

  loadChildren : function(el) {

    var children = [];
    var self = this;

    el.getChildren().each(function(item) {

      children.push(self.createElement(item));
    });

    this.setChildren(children);
  },

  loadChildrenOptions : function(el, options) {

    var children = [];
    var nodes = Array.from(el.getChildren());
    var subopt, node, next, tag, i, j;
    var length = nodes.length;

    for (i = j = 0; i < length && j < length; i++) {

      node = nodes[i];
      subopt = options.children[i];
      //next = options.children[i + 1];
      tag = node.get('tag');

      if (!subopt || tag !== subopt.name) {

        this.addDifference('missing', null, tag);

        i--;
        length--;
      }

      children.push(this.createElement(node, subopt));
    }

    this.setChildren(children);
  },

  createElement : function(node, options) {

    return new sylma.stepper.Element(node, options);
  },

  compare : function() {

    var result = true;
    var el = this.element;
    var opt = this.options;

    if (!opt) {

      return false;
    }
/*
    if (el.namespaceURI !== opt.namespace) {

      this.addDifference('namespace', el, opt.namespace);
      result = false;
    };
*/
    var size = el.getSize();

    var diff = {
      x : size.x - opt.size.x,
      y : size.y - opt.size.y
    };

    if (diff.x || diff.y) {

      this.addDifference('size', el, diff);
      result = false;
    }

    var position = el.getPosition();

    var diff = {
      x : position.x - opt.position.x,
      y : position.y - opt.position.y
    };

    if (diff.x || diff.y) {

      this.addDifference('position', el, diff);
      result = false;
    }

    if (result) {

      this.getChildren().each(function(item) {

        result = item.compare() && result;
      });
    }

    return result;
  },

  addDifference : function(type, el, expected) {

    console.log('difference', type, el, expected);
  },

  getAttributes : function() {

    var attrs = this.element.attributes;
    var length = attrs.length;
    var result = {};

    for (var i = 0; i < length; i++) {

      result[attrs[i].name] = attrs[i].value;
    }

    return result;
  },

  getPosition : function() {

    return this.element.getPosition();
  },

  getSize : function() {

    return this.element.getSize();
  },

  toJSON : function() {

    var el = this.element;

    return {
      name : el.get('tag'),
      //namespace : el.namespaceURI,
      attributes : this.getAttributes(),
      children : this.getChildren(),
      position : this.getPosition(),
      size : this.getSize()
    }
  },

  toString : function() {

    return JSON.stringify(this.toJSON());
  }
});

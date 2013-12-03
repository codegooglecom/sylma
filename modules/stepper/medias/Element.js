sylma.stepper.Element = new Class({

  children : [],
  content : undefined,
  ignore : undefined,
  ignoreMatch : /\d{2}\.\d{2}\.\d{4}/,

  initialize : function(el, options) {

    this.element = el;
    this.options = options;

    if (options) {

      if (options.children) {

        this.loadChildrenOptions(el, options);
      }

      this.ignore = options.ignore;
      this.content = options.content;
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

    var self = this;
    var children = el.getChildren();

    if (!children.length) {

      var text = el.get('text');
//console.log(text, text.match(/\d{2}\.\d{2}\.\d{4}/));
      if (text && text.match(this.ignoreMatch)) {

        this.ignore = true;
      }
      else {

        this.content = text;
      }
    } else {

      var result = [];

      children.each(function(item) {

        result.push(self.createElement(item));
      });

      this.setChildren(result);
    }


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

    if (!opt)
    {
      this.addDifference('No options');
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

    var content = this.content;

    if (content) {

      if (el.getChildren().length) {

        this.addDifference('text expected', el, content);
      }
      else {

        if (!this.ignore) {

          result = el.get('text') === content;

          if (!result) {

            this.addDifference('text different', el, content)
          }
        }
      }
    }
    else {

      this.getChildren().each(function(item) {

        result = item.compare() && result;
      });
    }

    return result;
  },

  addDifference : function(type, el, expected) {

    console.log('Difference : ', type, el, expected);
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
    var children = this.getChildren();
    var attributes = this.getAttributes();
    var result;

    result = {
      name : el.get('tag'),
      //namespace : el.namespaceURI,
      children : children.length ? children : undefined,
      content : this.content,
      ignore : this.ignore,
      attributes : attributes.length ? attributes : undefined,
      position : this.getPosition(),
      size : this.getSize()
    };

    return result;
  },

  toString : function() {

    return JSON.stringify(this.toJSON());
  }
});

/**
 *
 */

sylma.ui.Container = new Class({

  Extends : sylma.ui.Base,
  /*
  sylma : {
    template : {
      alias : undefined,
      mixed : false,
      classes : {}
    }
  },
  */

  initSylma : function(props) {

    var template = this.sylma.template;
    this.parent(props);

    Object.append(this.sylma.template, template);
  },

  update : function(args, path, inside) {

    if (inside !== undefined) this.set('sylma-inside', inside);

    var self = this;
    var path = path || this.get('path');

    var req = new Request.JSON({

      url : path + '.json',
      onSuccess : self.updateSuccess.bind(self)
    });

    req.get(args);
  },

  updateSuccess : function(response) {

    var result = sylma.ui.parseMessages(response);
    var name = this.getNode().getParent().tagName || 'div';
    var target;

    if (this.get('sylma-inside', false)) {

      target = this.getNode().getFirst();
    }
    else {

      target = this.getNode();
    }

    var node = sylma.ui.importNode(result.content, name);
    this.updateContent(result, node, target);
  },

  updateContent : function(result, node, target) {

    if (target) {

      node.replaces(target);

      var props = this.importResponse(result, this.getParent());
      this.initialize(props);
    }
    else {

      this.getNode().adopt(node);
      var props = this.importResponse(result, this.getParent(), true);
      if (props.objects) this.initObjects(props.objects);
    }
  },

  useTemplate : function() {

    return !!this.sylma.template;
  },

  isMixed : function() {

    return this.sylma.template && this.sylma.template.mixed;
  },

  initObjectContainer : function(props, key) {

    var result;

    if (!this.useTemplate()) {

      result = this.parent(props, key);
    }
    else {

      var tpl = props.sylma && props.sylma.template;
      var alias = tpl.alias;

      if (this.isMixed()) {

        result = this.parent(props, key);
      }
      else {

        if (!this.objects[alias]) {

          this.objects[alias] = [];
        }

        result = this.initObjectSplice(this.objects[alias], key);
      }
    }

    return result;
  },

  buildObject : function(alias, args, position) {

    var _class = this.sylma.template.classes[alias];

    if (!_class) {

      throw new Error('Cannot find class : ' + alias);
    }

    var props;
    var basic = {
      extend : _class.name,
      sylma : {
        template : {
          alias : alias
        }
      }
    };

    if (args) {

      props = args._init || {};
      delete args._init;

      props.options = args;

      props = Object.merge(basic, props);
    }
    else {

      props = basic;
    }

    var result = this.initObject(props, position);

    return result;
  },

  add : function(alias, args, key) {

    var target;
    var _class = this.sylma.template.classes[alias];
    var result = this.buildObject(alias, args, key);

    if (sylma.factory.debug) {

      sylma.log('Create "' + alias + '" to [' + key + ']');
    }

    if (result.sylma.splice) {

      ++key;

      var next = this.isMixed() ? this.tmp[key] : this.getObject(alias)[key];
      target = next.getNode();
    }
    else {

      target = this.getNode().getElement('.' + _class.node);
    }

    if (!target) {

      throw new Error('Target node ".' + _class.node + '" not found for sub object "' + alias + '"');
    }

    result.addTo(target);

    return result;
  },

  onWindowLoad : function() {

    if (this.useTemplate()) {

      if (this.isMixed()) {

        sylma.ui.loadArray(this.tmp);
      }
      else {

        Object.each(this.objects, function(item) {

          sylma.ui.loadArray(item);
        });
      }
    }
    else {

      this.parent();
    }
  },

  show : function(el) {

    el = el || this.getNode();

    el.addClass('sylma-visible');
  },

  /**
   * @uses Element.browserSupportVendorStyle()
   */
  hide : function(el, callback) {

    el = el || this.getNode();

    if (callback) {

      sylma.ui.addEventTransition(el, callback);
    }

    el.removeClass('sylma-visible');
  },

  toggleShow : function(el, val) {

    var result;
    el = el || this.getNode();

    if (val === false || (val === undefined && el.hasClass('sylma-visible'))) {

      result = false;
      this.hide(el);
    }
    else {

      result = true;
      this.show(el);
    }

    return result;
  },

  destroyChild : function(key, alias) {

    var container;

    if (!this.useTemplate() || this.isMixed()) {

      this.parent(key);
    }
    else {

      var container = this.objects[alias];

      container.splice(key, 1);
      container.slice(key).each(function(item, key) {

        item.setKey(key);
      });
    }

  }
});

Element.implement ({
    browserSupportStyle: function(style){
        var value = this.style[style];
        return !!(value || value == '');

    },
    browserSupportVendorStyle: function(style) {
        var prefixedStyle = null,
            capitalized = style.capitalize();
        return this.browserSupportStyle(style) ? style : ['webkit', 'Moz', 'O'].some(function(prefix) {
            prefixedStyle = prefix + capitalized;
            return this.browserSupportStyle(prefixedStyle)
        }, this) ? prefixedStyle : null;
    }
});

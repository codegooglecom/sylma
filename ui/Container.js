sylma.ui.ContainerProps = {

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

 updater : {
   running : false,
   obsolete : false,
   callback : null
 },

  initSylma : function(props) {

    var template = this.sylma.template;
    this.parent(props);

    Object.append(this.sylma.template, template);
  },

  update : function(args, path, inside, callback, get) {

    if (inside !== undefined) this.set('sylma-inside', inside);
    get = get || get === undefined;

    path = path || this.get('path');

    var req = new Request.JSON({
      data : args,
      method : get ? 'get' : 'post',
      url : path + '.json',
      onSuccess : function(response) {

        this.updateSuccess(response, callback);

      }.bind(this)
    });

    req.send();
  },

  updateSuccess : function(response, callback) {

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

    if (this.getNode().hasClass('hidder')) this.show();

    callback && callback();
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

    this.onWindowLoad.delay(10, this);
  },

  updateDelay: function(callback, delay) {

    if (this.updater.running) {

      this.updater.obsolete = true;
    }
    else {

      window.clearTimeout(this.updater.callback);

      this.updater.callback = function() {

        this.updater.running = true;
        callback();

      }.delay(delay, this);
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

    el.style.WebkitBackfaceVisibility = 'hidden';

    (function() {

      el.addClass('visible');
      el.addClass('sylma-visible'); // @deprecated


    }.delay(100));
  },

  /**
   * @uses Element.browserSupportVendorStyle()
   */
  hide : function(el, callback) {

    el = el || this.getNode();

    if (callback) {

      sylma.ui.addEventTransition(el, callback);
    }

    //el.style.WebkitBackfaceVisibility = 'hidden';

    (function() {

      el.removeClass('visible');
      el.removeClass('sylma-visible'); // @deprecated

    }.delay(100));
  },

  toggleShow : function(el, val) {

    var result;
    el = el || this.getNode();

    if (val === false || (val === undefined && (el.hasClass('sylma-visible') || el.hasClass('visible')))) {

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
};

sylma.ui.Container = new Class(sylma.ui.ContainerProps);

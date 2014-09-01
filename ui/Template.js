/**
 *
 */
sylma.ui.Template = new Class({

  Extends : sylma.ui.Container,

  initTemplate : function() {

    var el = sylma.ui.importNode(this.compileTemplate())[0];
    this.node = el;

    return el;
  },

  getAlias : function() {

    return this.sylma.template.alias;
  },

  compileTemplate : function() {

    var values = this.options;

    for (var i in this.sylma.template.autoloaded) {

      var alias = this.sylma.template.autoloaded[i];
      if (!values[alias]) values[alias] = [{}];
    }

    return this.buildTemplate(values);
  },

  convertChildren : function(children) {

    return typeOf(children) === 'object' ? Object.values(children) : children;
  },

  buildObjects : function(alias, objects) {

    return this.buildObjectsAll(objects, alias)
  },

  buildObjectsAll : function(objects, alias) {

    var result = [];
    objects = objects || [];

    this.convertChildren(objects).each(function(item) {

      var obj = this.buildObject(alias || item._alias, item);
      result.push(obj.compileTemplate());

    }.bind(this));

    return result.join('');
  },

  initNode : function(props, deep) {

    if (!props) {

      this.parent({
        id : this.id
      });
    }
    else if (props.sylma && props.sylma.template) {

      this.id = sylma.ui.generateID('sylma');
    }
    else {

      this.parent(props);
    }

    if (deep) {

      if (this.isMixed()) {

        this.tmp.each(function(item) {

          item.initNode(null, deep);
        });
      }
      else {

        Object.each(this.objects, function(collection) {

          collection.each(function(item) {

            item.initNode(null, deep);
          });
        });
      }
    }
  },

  onLoad : function() {},

  addTo : function(node) {

    var el = this.initTemplate();

    el.inject(node, 'before');

    this.initNode({node : el}, true);

    sylma.ui.loadArray([this]);
  },

  addChildren : function(children) {

    if (children) {

      Object.each(children, function(group, name) {

        Object.each(group, function(item) {

          this.add(name, item);

        }.bind(this));

      }.bind(this));
    }
  },

  destroy : function() {

    //this.parent();
    this.getParent().destroyChild(this.getKey(), this.getAlias());
  }

});

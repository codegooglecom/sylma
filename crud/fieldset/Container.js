
sylma.crud.fieldset = sylma.crud.fieldset || {};

sylma.crud.fieldset.Container = new Class({

  Extends : sylma.ui.Clonable,
  count : 0,

  initialize : function(props) {

    this.parent(props);

    if (this.getObject('content', false)) {

      this.count = this.getCount();
    }
  },

  prepare : function() {
  },

  clonePrepare : function() {

    this.parent();

    this.objects.add.clonePrepare();
    this.objects.content.clonePrepare();
  },

  getContent : function() {

    return this.getObject('content');
  },

  cloneContent : function(objects, tmp) {

    this.getNode().setStyle('display', 'block');

    var result = {};

    result.template = objects.template;
    result.add = objects.add.clone(this, this.getNode());
    result.content = objects.content.clone(this, this.getNode());

    this.objects = result;
  },

  getCount : function() {

    return this.getContent().getNode().getChildren().length;
  },

  addTemplate : function() {

    var row = this.createTemplate(this.count, this.getContent());
    this.count++;

    this.getContent().getNode().grab(row.getNode());
    this.getContent().tmp.push(row);

    setTimeout(function() {row.show()}, 1);
  },

  createTemplate : function(position, parent) {

    return this.getObject('template').clone(position, parent);
  },

  resetHighlight : function() {

    var rows = this.getContent().tmp;

    for (var i = 0; i < rows.length; i++) {

      rows[i].resetHighlight();
    }
  },

  highlight : function(sub) {

    var group = this.getContent().tmp[sub.key];

    if (!group) {

      throw new Error('Unknown group id : ' + sub.key);
    }

    return group.highlight(sub.alias);
  },

  downlight : function() {

    this.getParent().downlight();
  }
});
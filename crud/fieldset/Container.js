
sylma.crud.fieldset = {};

sylma.crud.fieldset.Container = new Class({

  Extends : sylma.ui.Container,
  count : 0,

  initialize : function(props) {

    this.parent(props);

    if (this.getObject('content', false)) {

      this.count = this.getCount();
    }
  },

  getContent : function() {

    return this.getObject('content');
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
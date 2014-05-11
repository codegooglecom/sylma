
sylma.crud.Group = new Class({

  Extends : sylma.ui.Clonable,
  elements : [],
  highlighted : 0,

  initialize : function(props) {

    this.parent(props);
    this.elements = Object.keys(this.objects);
  },

  resetHighlight : function() {

    this.highlighted = 0;
  },

  highlight : function(alias, sub) {

    var obj = this.getObject(alias, false);

    if (obj) {

      obj.highlight(sub);
      this.highlighted++;

      if (this.getCaller) {

        this.getCaller().getNode().addClass('field-statut-invalid');
      }
    }

    return obj;
  },

  downlight : function() {

    this.highlighted--;

    if (!this.highlighted) {

      this.getParent().downlight();

      if (this.getCaller) {

        this.getCaller().getNode().removeClass('field-statut-invalid');
      }
    }
  }
});
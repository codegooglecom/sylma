
sylma.crud.fieldset = sylma.crud.fieldset || {};

sylma.crud.fieldset.Row = new Class({

    Extends : sylma.crud.Group,
    position : 0,

    setPosition : function(pos) {

      this.position = pos;
    },

    cloneSub : function(obj) {

      var result = obj.clone(this, this.getNode(), this.position);

      return result;
    },

    downlight : function() {

      this.getParent().downlight();
    },

    remove : function() {

      Object.each(this.objects, function(item) {

        if (item && item.isHighlighted && item.isHighlighted()) {

          this.downlight();
        }
      }, this);

      var fieldset = this.getParent('fieldset');

      if (fieldset.get('useID')) {

        var id = this.getObject('id');
        var val = id.getValue();

        if (val) {

          id.setValue(- parseInt(val));

          this.hide(null, function() {

            this.getNode().inject(fieldset.getNode());

          }.bind(this));
        }
        else {

          this.parent();
        }
      }
      else {

        this.parent();
      }
    }
  });
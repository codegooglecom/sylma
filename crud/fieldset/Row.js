
sylma.crud.fieldset.Row = new Class({

    Extends : sylma.crud.Group,
    position : 0,

    setPosition : function(pos) {

      this.position = pos;
    },

    cloneContent : function(objects, tmp) {

      this.getNode().setStyle('display', 'block');

      var result = {
        objects : {},
        tmp : []
      }

      for (var i in objects) {

        result.objects[i] = this.cloneSub(objects[i]);
      }

      for (i = 0; i < tmp.length; i++) {

        result.tmp[i] = this.cloneSub(tmp[i]);
      }

      this.objects = result.objects;
      this.tmp = result.tmp;
    },

    cloneSub : function(obj) {

      var result = obj.clone(this, this.getNode(), this.position);

      return result;
    },

    downlight : function() {

      this.getParent().downlight();
    },

    remove : function() {

      for (var i in this.objects) {

        if (this.objects[i].isHighlighted()) {

          this.downlight();
        }
      }

      var fieldset = this.getParent('fieldset');

      if (fieldset.get('useID')) {

        var id = this.getObject('id');

        id.setValue(- parseInt(id.getValue()));

        this.hide(null, function() {

          this.getNode().inject(fieldset.getNode());

        }.bind(this));
      }
      else {

        this.parent();
      }
    },

  });

sylma.modules.todo.Task = new Class({

  Extends : sylma.ui.Container,
  side : false,

  toggleSide : function(refresh, remove, both) {

    var side = this.options.side;
    both = both === undefined ? true : both;
//console.log(side, refresh, remove, both);
    var form = this.getObject('form');
    var view = this.getObject('view');

    if (!side) {

      this.fixHeight(view.getNode().getSize().y);

      if (refresh) {

        //form.options.loaded = true;

        view.startLoading();

        form.update({id : this.options.id}, null, true, function() {

          this.toggleContainers(view.getNode(), form.getNode());

          //form.getNode().setStyle('marginTop', - view.getNode().getSize().y);
if(view.getObject('container')) {
          var input = form.getObject('form').getObject('description').getInput();
          var container = view.getObject('container').getNode('description');

          input.setStyle('height', container.getSize().y);
}
          this.fixHeight(form.getNode().getSize().y);
          this.toggleSides(form, view);

          view.stopLoading();

        }.bind(this));

      }
      else {

        this.fixHeight(form.getNode().getSize().y);
        this.toggleSides(form, view);
      }

    }
    else {

      //this.getNode().setStyle('minHeight', 0);
      //if (refresh) {

        this.toggleContainers(form.getNode(), view.getNode());
      //}

      if (both) {

        this.fixHeight(view.getNode().getSize().y);
      }
      else {

        this.fixHeight(0);
      }
      this.toggleSides(both && view, form);

      if (remove) {

        form.getObject('form').remove();
      }
    }

    this.options.side = !side;
  },

  toggleSides : function(side1, side2) {

    side1 && side1.getNode().toggleClass('active', true);
    side2 && side2.getNode().toggleClass('active', false);
  },

  toggleContainers: function (el1, el2) {

    var size = el1.getSize();

    el1.setStyles({
      width : size.x,
      height : size.y
    });

    el2.setStyles({
      width: null,
      height: null
    });
  },

  fixHeight : function(val) {

    this.getNode().setStyle('height', val);
  }

});
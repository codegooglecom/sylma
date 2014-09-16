
sylma.modules = sylma.modules || {};
sylma.modules.todo = {};

sylma.modules.todo.Explorer = new Class({

  Extends : sylma.ui.Container,

  onLoad : function() {

    $(window).addEvent('resize', this.updateCols.bind(this));
    this.updateCols();
  },

  updateCols : function() {

    var view = this.getNode();

    var two = 1000;
    var one = 600;

    var width = view.getSize().x;

    view.removeClass('one');
    view.removeClass('two');
    view.removeClass('three');

    if (width > two) {

      view.addClass('three');
    }
    else  if (width > one) {

      view.addClass('two');
    }
    else {

      view.addClass('one');
    }
  },

  createTask : function() {

    var container = this.getObject('new');

    container.update({}, null, true, function() {

      var form = container.tmp.pick();
      form.toggleSide();
    });
  },

  updateCollection : function(callback) {

    this.send(this.options.ids, {
      parent : this.options.parent
    }, function(response) {

      this.updatePositions(response, callback);
    }.bind(this));
  },

  updatePositions : function(response, callback) {

    var keys = JSON.decode(response.content[0]);
    var groups = this.getObject('collection').tmp;

    keys.slice(0, -1).each(function(groupKey, key) {

      var group = groups[key];
      var offset = 0;

      groupKey.slice(0, -1).each(function(id, subkey) {

        var task = group.tmp[subkey + offset];
//console.log(id, task && task.options.id);
        if (!task || task.options.id != id) {
//console.log(key, subkey, offset);
          var next = group.tmp[subkey + offset + 1];

          if (next && next.options.id == id) {
//console.log('remove');
            task.remove();
            //offset++;
          }
          else {
//console.log('add');
            offset--;
            var target = new Element('span');

            if (task) {

              task.getNode().grab(target, 'before');
            }
            else {

              this.getNode().grab(target);
            }

            this.send(this.options.task, {
              id : id
            }, function(response) {

              sylma.ui.parseMessages(response);
              var node = sylma.ui.importNode(response.content);

              var props = group.importResponse(response, group, true);
              node.replaces(target);
              var task = group.initObject(props.objects[Object.keys(props.objects).pick()], subkey + offset);

              task.options.side = true;
              task.toggleSide();

            }.bind(this), true);
          }
        }
      }.bind(this));
    }.bind(this));
  }

});

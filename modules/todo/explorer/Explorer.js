
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

    var datas = Object.clone(this.options.datas);
    datas.parent = this.options.parent;

    this.send(this.options.ids, datas, function(response) {

      //this._updatePositions(response, callback);
      this.updatePositions(response, callback);

    }.bind(this), true);
  },

  updatePositions : function(response, callback) {

    var keys = JSON.decode(response.content[0]);
    var groups = this.getObject('collection').tmp;

    var updates = [];
    var removes = [];
    var adds = [];

    keys.slice(0, -1).each(function(groupKey, key) {

      var group = groups[key];
      var offset = 0;

      groupKey.slice(0, -1).each(function(id, subkey) {

        var task = group.tmp[subkey + offset];
//console.log(id, task);

        if (task) {
//console.log('task');
          if (task.options.id == id) {
//console.log('==');
            if (task.disabled) {

              updates.push(task);
            }
          }
          else {
//console.log('!=');
            var next = group.tmp[subkey + offset + 1];

            if (next && next.options.id == id) {
//console.log('next');
              removes.push(task);
              offset++;
            }
            else {
//console.log('add1');
              adds.push({
                id : id,
                key : subkey,
                group : group,
                node : task.getNode()
              });
              offset--;
            }
          }
        }
        else {
//console.log('!task');
          adds.push({
            id : id,
            key : subkey,
            group : group
          });
        }
      }.bind(this));

      if (groupKey.length - 1 < group.length) {

        group.slice(groupKey.length - 1).each(function(task) {

          removes.push(task);
        });
      }

    }.bind(this));

    removes.each(function(item) {

      //item.toggleSide(true, true, false);

      var node = item.getNode();
      node.setStyle('height');

      item.remove();
    });

    adds.each(function(item) {

      var target = new Element('span');

      if (item.node) {

        item.node.grab(target, 'before');
      }
      else {

        item.group.getNode().grab(target);
      }

      this.addTask(item.id, item.key, item.group, target);

    }.bind(this));

    updates.each(function(item) {

      item.disabled = false;
      item.toggleSide(true, true);
      //item.checkView();
    });

  },

  updateList : function(args) {

    var datas = Object.append(this.options.datas, args);

    this.getObject('collection').update(datas);
  },

  addTask : function(id, key, group, target) {

    this.send(this.options.task, {
      id : id
    }, function(response) {

      sylma.ui.parseMessages(response);
      var node = sylma.ui.importNode(response.content);
//console.log('add', offset, subkey, target, node);
      var props = group.importResponse(response, group, true);
      node.replaces(target);
      var task = group.initObject(props.objects[Object.keys(props.objects).pick()], key);

      task.options.side = true;
      task.toggleSide();

    }.bind(this), true);
  }

});

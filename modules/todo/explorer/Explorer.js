
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

    var groupKey, group;
    var offset = 0;
    var update = false;
    var len = keys.length - 1;
    var start = 0;

    for (var key = 0; key < len; key++) {

      groupKey = keys[key];
      group = groups[key];
      offset = 0;
//console.log(key, len, keys.length, groupKey && groupKey.name, group && group.options.name);
      if (!groupKey || !group || groupKey.name != group.options.name) {

        update = true;
        break;
      }

      groupKey.tasks.slice(0, -1).each(function(id, subkey) {

        offset = this.compareTasks(group, id, subkey, offset, adds, removes, updates)

      }.bind(this));
//console.log(offset, groupKey.length - 1, group.tmp.length);
      if (!(groupKey.tasks.length - 1 < group.tmp.length) != !offset) {

        start = offset ? offset - 1: -1;
//console.log(key);
        group.tmp.slice(groupKey.tasks.length + start).each(function(task) {

          removes.push(task);
        });
      }
    }

    if (update) {

      this.updateList();
    }
    else {

      this.applyTasks(adds, removes, updates);
    }
  },

  compareTasks : function(group, id, subkey, offset, adds, removes, updates) {

    var task = group.tmp[subkey + offset];

    if (task) {

      if (task.options.id == id) {

        if (task.disabled) {

          updates.push(task);
        }
      }
      else {

        var next = group.tmp[subkey + offset + 1];

        if (next && next.options.id == id) {

          removes.push(task);
          offset++;
        }
        else {

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

      adds.push({
        id : id,
        key : subkey,
        group : group
      });
    }

    return offset;
  },

  applyTasks: function(adds, removes, updates) {

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

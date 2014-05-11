sylma.ui.ClonableProps = {

  Extends : sylma.ui.Container,

  initialize : function(props) {

    this.props = props;
    this.parent(props);
  },

  clonePrepare : function() {

    var id = 'sylma' + Math.floor(Math.random(new Date().getSeconds()) * 999);
    this.getNode().addClass(id);

    this.cloneID = id;
  },

  clone : function(parent, node) {

    var props = {
      extend : this.props.extend,
      events : this.props.events,
      sylma : {}
    };

    props.node = node.getElements('.' + this.cloneID).pick();

    if (!props.node) {

      throw new Error('Cannot find node for clone');
    }

    props.id = null;
    props.parentObject = parent;
    props.sylma.isclone = true;

    var result = sylma.ui.createObject(props);

    result.cloneContent(this.objects, this.tmp);

    return result;
  },

  cloneContent : function(objects, tmp) {

    this.getNode().setStyle('display', 'block');

    var result = {
      objects : {},
      tmp : []
    };

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

    var result = obj.clone(this, this.getNode());

    return result;
  }
};

sylma.ui.Clonable = new Class(sylma.ui.ClonableProps);

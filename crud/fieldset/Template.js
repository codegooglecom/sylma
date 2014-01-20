
sylma.crud.fieldset.Template = new Class({

  Extends : sylma.crud.fieldset.RowMovable,

  initialize : function(props) {

    this.parent(props);

    this.props = props;
    this.prepare();
  },

  prepare : function() {

    var objects = this.tmp.slice(0);
    Object.each(this.objects, function(item) {
      objects.push(item);
    });

    for (var i = 0; i < objects.length; i++) {

      objects[i].clonePrepare();
    }
  },

  clone : function(position, parent) {

    var props = this.props;
    parent = parent || this;

    //props.parentObject = parent;
    //props.sylma.parents = Object.append({}, this.getParents());

    props.objects = {};
    props.sylma.key = null;

    props.node = this.getNode().clone(true);

    var clone = sylma.ui.createObject(props);

    clone.setPosition(position);
    clone.cloneContent(this.objects, this.tmp);

    return clone;
  }
});

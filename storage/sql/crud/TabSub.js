
sylma.crud.multiform.TabSub = new Class({

  Extends : sylma.crud.multiform.Tab,

  createItem : function(e) {

    var tabs = this.getParent('tabs');
    var key = this.get('key');
    var path = this.get('path');
    var id = this.getParent('handler').getID();

    tabs.go(key, function() {

      tabs.getTab(key).getObject('form').update({parent: id}, path);
    });

    e.stopPropagation();
  }
})
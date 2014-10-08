
sylma.crud = sylma.crud || {};
sylma.crud.multiform = sylma.crud.multiform || {};

sylma.crud.multiform.Tab = new Class({

  Extends : sylma.ui.tab.Caller,

  createItem : function(e) {

    var tabs = this.getParent('tabs');
    var key = this.get('key');
    var path = this.get('path');

    tabs.go(key, function() {
console.log(tabs);
      tabs.getTab(key).getObject('form').update({}, path);
    });

    e.stopPropagation();
  }
})
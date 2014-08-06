
sylma.uploader.MainList = new Class({

  Extends : sylma.uploader.Main,

  sendComplete : function(response) {

    var id = response.content;

    if (!response.error && id) {

      var tab = this.getParent('tab');
      var list = tab.getObject('list');

      list.update();
      tab.getObject('form').update({id : id}, list.get('update'));
    }

    this.parent();
  }

});
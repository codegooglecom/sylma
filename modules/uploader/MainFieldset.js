
sylma.uploader.MainFieldset = new Class({

  Extends : sylma.uploader.MainPosition,

  sendComplete : function(response) {

    this.parent(response);

    if (response.content) {
/*
      var container = this.fieldset.getObject('content');

      sylma.ui.importNode(response.content).inject(container.getNode());

      var props = this.importResponse(response, this);

      container.initObject(props);
*/

      //var result = sylma.ui.parseMessages(response);
      var node = sylma.ui.importNode(response.content);

      this.fieldset.getObject('content').updateContent(response, node);

      this.position++;
    }
    else {

      //sylma.ui.showMessage('No valid response');
      //throw new Error('No valid response');
    }

    this.fireEvent('complete');
  },
});
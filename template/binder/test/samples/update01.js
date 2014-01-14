
sylma.samples = sylma.samples || {};

sylma.samples.Sample2 = new Class({

  Extends : sylma.ui.Container,

  updateSuccess : function(response) {

    this.parent(response);

    //sylma.tester.test(true);
    sylma.tester.assertEquals(this.getNode().get('text'), 'Hello world !');
    sylma.tester.test(true);
  }
});
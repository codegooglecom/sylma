
sylma.samples = sylma.samples || {};

sylma.samples.Sample2 = new Class({

  Extends : sylma.ui.Container,

  run : function() {

    this.update();
  },

  updateSuccess : function(response) {

    this.parent(response);

    sylma.tester.assertEquals(this.tmp.length, 2);
    sylma.tester.assertEquals(this.getNode().get('text'), 'HelloWorld');
    sylma.tester.test(true);
  }
});
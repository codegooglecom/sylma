sylma.classes.tester = new Class({

  run : function(test, bind) {

    var result;

    try {

      result = test.call(bind);
    }
    catch (e) {

      result = false;
      console.log(e);
    }

    $(document.body).grab(new Element('input', {
      type : 'hidden',
      id : 'sylma-test-result',
      value : result ? 1 : ''
    }))
  }
});

sylma.tester = new sylma.classes.tester();

var example = {};
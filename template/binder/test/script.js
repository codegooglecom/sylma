var sylma = sylma || {
  classes : {}
};

sylma.classes.binder = new Class({

  current : 0,
  tests : {},
  id : '',
  url : '',

  initialize : function() {


  },

  run : function() {

    this.node = $(this.id);
    this.loadNext();
  },

  loadNext : function() {

    var test = this.tests[this.current];
    if (test) this.runTest(test);

    this.current++;
  },

  runTest : function(test) {

    var frame = new IFrame({

      src : this.standalone + '?file=' + test.file + '&key=' + test.key,
      name : 'testframe',
      styles : {
        display : 'none'
      },
      events : {
        load : function() {

          var doc = this.contentDocument || this.contentWindow.document;
          var timeStart = new Date().getTime();
          var timeMax = 1000;
          var input;

          while (!input && (new Date().getTime() - timeStart) < timeMax) {

            input = doc.getElement('#sylma-test-result');
          }

          var result = {
            value : false,
            timemax : false
          };

          if (input) result.value = (input.value);
          else result.timemax = true;

          sylma.binder.loadResult(test, result);
        }
      }
    });

    this.node.grab(frame);
  },

  loadResult : function(test, result) {

    var content = result.value ? 'ok' : 'failed';
    if (result.timemax) content += ' (time elapsed)';

    var className = 'sylma-test-' + (result.value ? 'success' : 'failed');


    this.node.grab(new Element('div', {
      html : '<li class="' + className + '"><span>' + test.name + '</span> - <span class="sylma-tester-result">' + content + '</span></li>',
    }));

    this.loadNext();
  }
});

sylma.binder = new sylma.classes.binder();
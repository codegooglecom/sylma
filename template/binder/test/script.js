var sylma = sylma || {
  classes : {}
};

sylma.classes.binder = new Class({

  current : 0,
  tests : {},
  id : '',
  url : '',
  timeMax : 2000,

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

    var self = this;
    console.log('test ' + test.key);

    var frame = new IFrame({

      src : this.standalone + '?file=' + test.file + '&key=' + test.key,
      name : 'testframe',
      styles : {
        display : 'none'
      },
      events : {
        load : function() {

          var doc = this.contentDocument || this.contentWindow.document;

          if (doc && doc.getElement) {

            var timeStart = new Date().getTime();
            var input;

            var loop = window.setInterval(function() {

              input = doc.getElement('#sylma-test-result');

              if (input || (new Date().getTime() - timeStart) > self.timeMax) {

                window.clearInterval(loop);
                self.finishLoop(test, frame, input);
              }

            }, 200);
          }
          else {

            self.finishLoop(test, frame);
          }
        }
      }
    });

    this.node.grab(frame);
  },

  finishLoop : function(test, frame, input) {

    var value;

    if (!input) {

      value = {value : false}
    }
    else {

      value = JSON.decode(input.get('value'));
    }

    sylma.binder.loadResult(test, value, frame.src);
  },

  loadResult : function(test, result, href) {

    var content = result.value ? 'ok' : 'failed';
    if (result.timemax) content += ' (time elapsed)';

    var className = 'sylma-test-' + (result.value ? 'success' : 'failed');


    this.node.grab(new Element('div', {
      html : '<li class="' + className + '"><a href="' + href + '">' + test.name + '</a> - <span class="sylma-tester-result">' + content + '</span></li>',
    }));

    this.loadNext();
  }
});

sylma.binder = new sylma.classes.binder();
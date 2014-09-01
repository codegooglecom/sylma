
sylma.tester.TestJS = new Class({

  Extends : sylma.tester.Test,

  timeMax : 2000,

  test : function(callback) {

    var frame = this.getFrame();
    var test = this;

    this.isReady(true);
    this.isPlayed(false);
    this.hasError(false);

    frame.removeEvents();
    frame.addEvent('load', function() {

      var doc = this.contentDocument || this.contentWindow.document;

      if (doc && doc.getElement) {

        var timeStart = new Date().getTime();
        var input;

        var loop;
        loop = function() {

          input = doc.getElement('#sylma-test-result');

          if (input || (new Date().getTime() - timeStart) > this.timeMax) {

            window.clearInterval(loop);
            this.finishLoop(callback, input);
          }

        }.periodical(200, test);

      }
      else {

        test.finishLoop(callback);
      }
    });

    var path = this.getParent('main').get('module').standalone;
    var file = this.getParent('file').get('path');
    var key = this.get('id');

    frame.src = path + '?file=' + file + '&key=' + key;
  },

  finishLoop : function(callback, input) {

    this.isReady(false);

    this.getFrame().removeEvents();
    this.getParent('main').prepareFrame(this.getFrame());

    var response;

    if (!input) {

      response = {value : false}
    }
    else {

      response = JSON.decode(input.get('value'));
    }

    if (response.timemax) {

      console.log('time elapsed');
    }

    if (response.value) {

      this.isPlayed(true);
    }
    else {

      this.addError('Test failed');
    }

    callback && callback();
  }
});
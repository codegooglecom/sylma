sylma.tester = {

  test : function(result) {

    if (result) {

      $(document.body).grab(new Element('input', {
        //type : 'hidden',
        id : 'sylma-standalone-result',
        value : result ? 1 : ''
      }));
    }
  },

  classes : {

    Main : new Class({

      test : null,

      run : function(test, bind, callback) {

        var result;

        try {

          result = test.call(bind);
          if (callback) this.loop();
        }
        catch (e) {

          console.log(e);
          sylma.tester.test(false);
        }

        if (!callback) {

          sylma.tester.test(result);
          this.grabResult({value : result, timemax : false});
        }
      },

      loop : function() {

        var timeStart = new Date().getTime();
        var timeMax = 1000;
        var input;

        while (!input && (new Date().getTime() - timeStart) < timeMax) {

          input = $('sylma-standalone-result');
        }

        var result = {
          value : false,
          timemax : false
        };

        if (input) result.value = (input.value);
        else result.timemax = true;

        this.grabResult(result);
      },

      grabResult : function(result) {

        $(document.body).grab(new Element('input', {
          //type : 'hidden',
          id : 'sylma-test-result',
          value : JSON.stringify(result)
        }));
      }
    })
  }
};

sylma.tester.main = new sylma.tester.classes.Main;

var example = {};
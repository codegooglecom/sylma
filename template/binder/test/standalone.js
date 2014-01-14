sylma.tester = {

  test : function(result) {

    $(document.body).grab(new Element('input', {
      //type : 'hidden',
      id : 'sylma-standalone-result',
      value : result ? 1 : ''
    }));
  },

  assertStrictEquals : function(val1, val2) {

    val1 = JSON.stringify(val1);
    val2 = JSON.stringify(val2)

    return val1 === val2;
  },

  assertEquals : function(val1, val2, log) {

    var result = true;

    var type1 = typeOf(val1);
    var type2 = typeOf(val2);

    if (type1 === 'object' && type2 === 'object') {

      result = val1 == val2;
    }
    else if (type1 === 'array' && type2 === 'array') {

      result = val1.toString() === val2.toString();
    }
    else {

      result = val1 === val2;
    }

    if (!result) {

      this.test(false);
      console.log(val1, val2);
      throw new Error('Values not equals in ' + log);
    }
  },

  classes : {

    Main : new Class({

      test : null,
      timeMax : 2000,

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
        var input;
        var self = this;

        var loop = window.setInterval(function() {

          input = $('sylma-standalone-result');

          if (input || (new Date().getTime() - timeStart) > self.timeMax) {

            window.clearInterval(loop);

            var result = {
              value : false,
              timemax : false
            };

            if (input) result.value = (input.value);
            else result.timemax = true;

            self.grabResult(result);
          }

        }, 200);
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
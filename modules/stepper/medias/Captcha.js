sylma.stepper.Captcha = new Class({

  Extends : sylma.stepper.Input,

  loaded : false,

  getValue : function(callback) {

    var result;

    if (callback) {

      this.loaded = true;
      this.send(this.getParent('main').get('captcha'), {}, function(response) {

        this.options.value = response.content;
        callback && callback();

      }.bind(this));
    }
    else {

      result = this.options.value;
    }

    return result;
  },

  activate: function(callback) {

    var selector = this.add('selector');

    selector.activate(function() {

      this.updateElement(callback);

    }.bind(this));
  },

  updateElement: function(callback) {

    this.getValue(function() {

      this.$caller = this.updateElement;
      this.parent();

      callback && callback();

    }.bind(this), true);
  },

  test : function(callback) {

    this.log('Test');

    //this.isReady(false);
    //this.isPlayed(true);

    this.updateElement(callback);
  },

  toJSON : function() {

    return {captcha : {
      '@element' : this.getSelector()
    }};
  }
});

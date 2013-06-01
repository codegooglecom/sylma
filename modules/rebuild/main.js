
sylma.rebuild = {

  classes : {

    Main : new Class({

      enable : false,

      initialize : function() {

      },

      toggle : function() {

        this.enable = !this.enable;

        if (this.enable) this.load();
        $('rebuild-toggle').set('html', this.enable ? 'Stop' : 'Start');
      },

      load : function() {

        var int;
        var files = $$('.rebuild-file');
        var count = 0;
        var self = this;

        files.addClass('rebuild-ready');
        files.removeClass('rebuild-load');
        files.removeClass('rebuild-complete');

        var callback = function() {

          var file = files[count];
          var path = file.get('user-data');

          file.removeClass('rebuild-ready');
          file.addClass('rebuild-load');

          var jsonRequest = new Request.JSON({
            url: '/sylma/modules/rebuild/standalone.json',
            onSuccess: function(result) {
              //sylma.ui.parseMessages(result);
            },
            onComplete : function() {

              file.removeClass('rebuild-load');
              file.addClass('rebuild-complete');

              if (self.enable) callback();
            },
          }).get({path : path});

          if (count > files.length) window.clearInterval(int);
//if (count === 1)window.clearInterval(int);
          count++;
        };

        //int = window.setInterval(callback, 5000);
        callback();
      }
    })
  }
}

sylma.rebuild.main = new sylma.rebuild.classes.Main();
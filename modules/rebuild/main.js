
sylma.rebuild = {

  classes : {

    Main : new Class({

      enable : false,

      initialize : function() {

      },

      toggle : function(stop) {

        this.enable = !this.enable;

        if (this.enable && !stop) this.load();
        $('sylma-rebuild-toggle').set('html', this.enable || stop ? 'Stop' : 'Start');
      },

      clearLog : function() {

        $('sylma-rebuild-log').set('html', '');
      },

      load : function() {

        var int;
        var files = $$('.rebuild-file');
        var count = 0;
        var self = this;

        files.addClass('rebuild-ready');
        files.removeClass('rebuild-load');
        files.removeClass('rebuild-complete');

        this.clearLog();

        var callback = function() {

          var file = files[count];
          var path = file.get('user-data');

          file.removeClass('rebuild-ready');
          file.addClass('rebuild-load');

          var jsonRequest = new Request.JSON({
            url: '/sylma/modules/rebuild/standalone.json',
            onSuccess: function(result) {

              sylma.ui.addMessage(path);
              sylma.ui.parseMessages(result, $('sylma-rebuild-log'));
            },
            onComplete : function() {

              file.removeClass('rebuild-load');
              file.addClass('rebuild-complete');

              if (self.enable) callback();
              else self.toggle(true);
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
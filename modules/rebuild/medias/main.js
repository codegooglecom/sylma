
sylma.rebuild = {

  classes : {

    Main : new Class({

      enable : false,

      initialize : function() {

      },

      toggle : function(stop) {

        if (stop) this.enable = false;
        else this.enable = !this.enable;

        if (this.enable) this.load();
        $('sylma-rebuild-toggle').set('html', this.enable ? 'Stop' : 'Start');
      },

      resetFiles : function(files) {

        var classes = ['complete', 'success', 'error'];

        for (var i = 0; i < classes.length; i++) {

          files.removeClass('completed').removeClass('rebuild-' + classes[i]);
        }

        return files;
      },

      clearLog : function() {

        var files = this.getFiles();

        $('sylma-rebuild-log').set('html', '');
        this.resetFiles(files);

        return files;
      },

      getFiles : function() {

        return $$('.rebuild-file');
      },

      load : function() {

        //var int;
        var files = this.clearLog();
        var count = 0;
        var self = this;

        files.addClass('rebuild-ready');


        var callback = function() {

          var file = files[count];
          var path = file.get('user-data');

          file.removeClass('rebuild-ready');
          file.addClass('rebuild-load');

          new Request.JSON({
            url: '/sylma/modules/rebuild/standalone.json',
            onSuccess: function(result) {
//console.log('success');
              sylma.ui.parseMessages(result, $('sylma-rebuild-log'));

              file.addClass('rebuild-success');
            },
            onComplete : function() {
//console.log('complete');
              sylma.ui.addMessage(path);

              file.removeClass('rebuild-load');
              file.addClass('rebuild-complete');

              if (self.enable && count !== files.length) {

                callback();
              }
              else {

                self.toggle(true);
              }
            },
            onFailure : function() {
//console.log('failure');
            },
            onError : function() {
//console.log('error');
              file.addClass('rebuild-error');
              this.fireEvent('complete');
            }
          }).get({path : path});

          count++;
        }

        callback();
      }
    })
  }
}

sylma.rebuild.main = new sylma.rebuild.classes.Main();
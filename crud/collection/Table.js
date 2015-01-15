sylma.crud = sylma.crud || {};
sylma.crud.collection = sylma.crud.collection || {};

sylma.crud.collection.Table = new Class({

  Extends : sylma.crud.Form,

  updating : false,
  obsolete : false,
  delay : 200,

  update : function(reset, delay) {

    if (this.updating) {

      this.obsolete = true;
    }
    else {

      window.clearTimeout(this.updater);

      delay = delay ? this.delay : 0;

      this.updater = function() {

        this.updating = true;

        if (reset) {

          var pager = this.getObject('container').getObject('pager');
          
          if (pager) {

            pager.setPage(1);
          }
        }

        this.submit();

      }.delay(this.delay, this);
    }
  },

  submitReturn : function(response) {

    this.getObject('container').updateSuccess(response);
    this.hideMask();

    this.updating = false;

    if (this.obsolete) {

      this.obsolete = false;
      this.update(true);
    }
  },

  getInputs : function() {

    return this.getObject('container').getNode().getElements('input, select, textarea');
  },
});


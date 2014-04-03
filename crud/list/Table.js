sylma.crud = sylma.crud || {};
sylma.crud.list = sylma.crud.list || {};

sylma.crud.list.Table = new Class({

  Extends : sylma.crud.Form,

  updating : false,
  obsolete : false,
  delay : 200,

  onLoad : function() {

    this.getObject('head').tmp.each(function(head) {
      head.updateOrder();
    });
  },

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

          this.getObject('container').getObject('pager').setPage(1);
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


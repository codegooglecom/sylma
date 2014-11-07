
sylma.ui.Morph = new Class({

  Implements : Events,

  /**
   * [Events] : openPrepare, closePrepare, closeComplete
   */

  node : null,
  position : null,
  size : null,
  saved : false,
  toggleStat : false,
  target : {
    position : 'absolute',
/*
    left : null,
    top : null,
    width : null,
    height : null,
*/
    transition : 'width'
  },
  cssDelay : 50,
  reset : null,

  initialize : function(node, target, dummy) {

    this.node = node;

    if (!dummy) {

      dummy = new Element('div.dummy');
      this.node.grab(dummy, 'after');
    }

    this.dummy = dummy;

    if (target) {

      Object.append(this.target, target);
    }
  },

  toggle : function(target, reset) {

    this.toggleStat = !this.toggleStat;

    if (this.toggleStat || target) {

      if (target) {

        target = Object.append(Object.append({}, this.target), target);
      }
      else {

        target = this.target;
      }

      this.open(target, reset);
    }
    else {

      this.close();
    }
  },

  open : function(target, reset) {

    var node = this.node;

    var size = node.getSize();
    var position = node.getPosition();

    this.dummy.setStyles({
      //width : size.x,
      height : size.y
    });

    node.setStyles({
      position : target.position,
      left : position.x,
      top : position.y,
      width : size.x,
      height : size.y
    });

    (function() {

      node.addClass('animate');
      this.fireEvent('openStart', node);
      //this.fireEvent.delay(20, this, ['openStart', node]);

      if (reset) {

        this.attachReset(node, reset);
      }
      else {

        this.fireEvent('openComplete');
      }

      node.setStyles(Object.subset(target, ['left', 'top', 'width', 'height']));

      this.fireEvent('openPrepare', node);
      //this.fireEvent.delay(20, this, ['openPrepare', node]);

    }.delay(this.cssDelay, this, node));
  },

  attachReset : function(node, reset) {

    sylma.ui.addEventTransition(node, function() {

      node.setStyles(reset);
      this.fireEvent('openComplete');

    }.bind(this), this.target.transition, true);
  },

  close : function() {

    var node = this.node;
    var target = this.target;
    var dummy = this.dummy;

    var callback = this.closeReset.bind(this);
    sylma.ui.addEventTransition(node, callback, this.target.transition, true);

    var position = dummy.getPosition();
    var size = dummy.getSize();

    node.setStyles({
      left : position.x,
      top : position.y,
      width : size.x,
      height : size.y
    });

    this.fireEvent('closePrepare');
  },

  closeReset : function() {

    var node = this.node;
    var dummy = this.dummy;

    node.removeClass('animate');

    node.setStyles({
      left : null,
      top : null,
      width : null,
      position : null
    });

    dummy.setStyle('height', 0);
    this.fireEvent('closeComplete');
  }

});
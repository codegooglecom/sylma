
sylma.crud.Foreign = new Class({

  Extends : sylma.crud.Field,

  current : 0,

  updateValueContent : function(key, val, content) {

    this.current = key;
    val = val > 0 ? val : '';

    this.setValue(val);
    this.getNode().toggleClass('active', val);
    this.getNode('value').set('html', content);
  },

  updateValue : function(key, id, content) {

    this.hideContainer();
    this.updateValueContent(key, id, content);
  },

  getItems : function() {

    return this.getObject('container').getNode().getChildren();
  },

  getItem : function() {


  },

  stepValue : function(step) {

    var val = this.current;
    var items = this.getItems();
    step = this.get('suffix') === 'min' ? step : - step;

    if (step < 0) {

      val--;
      if (val < 0) val = items.length - 1;
    }
    else {

      val++;
      if (val >= items.length) val = 0;
    }

    var el = items[val];

    return this.stepValueNode(el);
  },

  stepValueNode: function (el) {

    return this.updateValue(el.get('data-key'), el.get('data-id'), el.get('html'));
  },

  showContainer : function() {

    this.getNode().addClass('open');
    this.getObject('container').show();
  },

  hideContainer : function() {

    this.getNode().removeClass('open');
    this.getObject('container').hide();
  }
});
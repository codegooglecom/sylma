
sylma.Browser = new Class({
/*
  getStyle : function(name) {

    switch (Browser.name) {

      case 'ie' :
      case 'firefox' :

        result = name;
        break;

      case '' :

    }
  },
*/
  isTouched : function() {

    return sylma.ui.isTouched();
  },

  parseEvents : function(events) {

    if (this.isTouched()) {

      return events;
    }

    var result = {};

    Object.each(events, function(item, key) {

      switch (key) {

        case 'touchstart' : key = 'mousedown'; break;
        case 'touchend' : key = 'mouseup'; break;
        case 'touchmove' : key = 'mousemove'; break;
      }

      result[key] = item;
    });

    return result;
  },

  getPosition : function(e) {

    var result;

    if (this.isTouched()) {

      result = {
        x : e.touches[0].pageX,
        y : e.touches[0].pageY
      };
    }
    else {

      result = {
        x : e.pageX,
        y : e.pageY
      };
    }

    return result;
  }
});
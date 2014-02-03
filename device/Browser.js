//sylma.device = sylma.device || {};
var sylma = sylma || {};
sylma.device = {};

sylma.device.Browser = new Class({
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
  },

  /**
   * @from https://gist.github.com/nateps/1172490
   * @required #body
   */
  setupScroll : function() {

    //window.scrollTo(0, 1);
    var ua = navigator.userAgent,
        iphone = ~ua.indexOf('iPhone') || ~ua.indexOf('iPod'),
        ipad = ~ua.indexOf('iPad');

    var ua = this.ua = {
      page : $('body'),
      iphone : iphone,
      ipad : ipad,
      ios : iphone || ipad,
      android : ~ua.indexOf('Android'),
      fullscreen : window.navigator.standalone,
    };

    if (ua.android) {

      window.onscroll = function() {

        ua.page.style.height = window.innerHeight + 'px'
      };
    }

    this.scrollTop();
  },

  scrollTop : function() {

    var ua = this.ua;
    // Start out by adding the height of the location bar to the width, so that
    // we can scroll past it
    if (ua.ios) {
      // iOS reliably returns the innerWindow size for documentElement.clientHeight
      // but window.innerHeight is sometimes the wrong value after rotating
      // the orientation
      var height = document.documentElement.clientHeight;
      // Only add extra padding to the height on iphone / ipod, since the ipad
      // browser doesn't scroll off the location bar.
      if (ua.iphone && !ua.fullscreen) height += 60;
      ua.page.style.height = height + 'px';

    } else if (ua.android) {
      // The stock Android browser has a location bar height of 56 pixels, but
      // this very likely could be broken in other Android browsers.
      page.style.height = (window.innerHeight + 56) + 'px'
    }
    // Scroll after a timeout, since iOS will scroll to the top of the page
    // after it fires the onload event
    setTimeout(scrollTo, 0, 0, 1);
  },

  /**
   * Allow standalone application on IOS
   * @from https://gist.github.com/kylebarrow/1042026
   */
  resetLinks : function() {

    // Mobile Safari in standalone mode
    if(("standalone" in window.navigator) && window.navigator.standalone){

      // If you want to prevent remote links in standalone web apps opening Mobile Safari, change 'remotes' to true
      var noddy, remotes = false;

      document.addEventListener('click', function(event) {

        noddy = event.target;

        // Bubble up until we hit link or top HTML element. Warning: BODY element is not compulsory so better to stop on HTML
        while(noddy.nodeName !== "A" && noddy.nodeName !== "HTML") {
          noddy = noddy.parentNode;
        }

        if('href' in noddy && noddy.href.indexOf('http') !== -1 && (noddy.href.indexOf(document.location.host) !== -1 || remotes))
        {
          event.preventDefault();
          document.location.href = noddy.href;
        }

      },false);
    }
  }
});
/* $Id$ : */

sylma['dbx-classes'] = {
  
  'main' : new Class({
    
    Extends : sylma.classes.layer,
    
    updateOrder : function(sOrder) {
      
      var options = {'order' : sOrder, 'page' : 1};
      
      if (sOrder == this.order) {
        
        this.orderDir = this.orderDir == 'a' ? 'd' : 'a'
        options['order-dir'] = this.orderDir;
        
      } else {
        
        if (this.order && this.headers[this.order]) this.headers[this.order].node.removeClass('current');
        if (this.headers[sOrder]) this.headers[sOrder].node.addClass('current');
        
        this.orderDir  = 'a';
        this.order = sOrder;
      }
      
      //sylma.dsp(sOrder + ' / ' + this.order);
      
      this.lister.update(options, {'method' : 'get'});
      return false;
    }
    
  }),
  
  'lister' : new Class({
    
    Extends : sylma.classes.layer,
    
    updatePage : function(iPage) {
      
      this.update({'page' : iPage}, {'method' : 'get'});
      return false;
    },
    
    updateOrder : function(sOrder) {
    },
    
    lastPage : function() {
      
      return this.updatePage(parseInt(this.last));
    },
    
    previousPage : function() {
      
      return this.updatePage(parseInt(this.current) - 1);
    },
    
    nextPage : function() {
      
      return this.updatePage(parseInt(this.current) + 1);
    }
  })
};

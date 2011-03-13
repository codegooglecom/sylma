/* Document JS */

Locale.use('fr-FR');

sylma['dbx-classes'] = {
  
  form : new Class({
    
    Extends : sylma.classes.layer,
    dateInputFormat : '%Y-%m-%d',
    
    initialize : function(options) {
      
      this.parent(options);
      
      var self = this;
      
      $$('form textarea').each(function(el) {
        
        if (el.get('text') == ' ') el.empty();
      });
      
      $$('input.field-input-date').each(function(el) {
        
        var sDate = el.getAttribute('value');
        var date = Date.parse(sDate);
        if (date) el.setAttribute('value', date.format(self.dateShowFormat));
        
        new Picker.Date(el, {
          
          pickerClass : 'datepicker_jqui',
          format : self.dateShowFormat,
          onSelect: function(date){
            
            el.getNext().set('value', date.format(self.dateInputFormat));
          }
        });
      });
      // });
    }
    
  }),
  
  complex : new Class({
    
    Extends : sylma.classes.layer
    
  }),
  
  template : new Class({
    
    Extends : sylma.classes.layer,
    
    add : function() {
      
      this.parentObject.insert({
        'html' : this.node,
        'html-position' : 'before',
        'path' : this.rootObject.pathAdd,
        'arguments' : { path : this.path }
      });
    }
  })
};

sylma['dbx-classes'].form.implement({
  dateShowFormat : '%e%o %B %Y'
});
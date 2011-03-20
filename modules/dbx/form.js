/* Document JS */

Locale.use('fr-FR');

addWindowLoad(function () {
  
  $$('form textarea').each(function(el) {
    
    if (el.get('text') == ' ') el.empty();
  });
  
  $$('input.field-input-date').each(function(el) {
    
    var showFormat = '%e%o %B %Y';
    var inputFormat = '%Y-%m-%d';
    
    var date = el.get('value') ? Date.parse(el.get('value')) : '';
    var input = el.getNext();
    
    if (date) el.setAttribute('value', date.format(showFormat));
    
    var options = {
      
      pickerClass : 'datepicker_jqui',
      format : showFormat,
      onSelect: function(date){
        
        input.set('value', date.format(inputFormat));
      }
    };
    
    // extract options set by lc:view-options
    
    var option, inputOptions = $('sylma-options-' + input.get('name'));
    
    if (inputOptions && inputOptions.get('value')) {
      
      inputOptions.get('value').split(';').each(function(aOption){
        
        option = aOption.split(':');
        options[option[0]] = option[1];
      });
    }
    
    new Picker.Date(el, options);
  });
});




sylma.crud = sylma.crud || {};

Locale.use('fr-FR');

sylma.crud.Date = new Class({

  Extends : sylma.crud.Field,

  onLoad : function() {

    this.loadWidget();
  },

  loadWidget : function() {

    var el = this.getNode().getElement('input[type=text]');

    var showFormat = '%e%o %B %Y';
    var inputFormat = '%Y-%m-%d'; //%H:%m:%S

    var date = el.get('value') ? Date.parse(el.get('value')) : '';
    var input = el.getNext();

    if (date) el.setAttribute('value', date.format(showFormat));

    var options = {

      pickerClass : 'datepicker_jqui',
      format : showFormat,
      yearsPerPage : 16,
      //columns : 5,
      onSelect: function(date){

        input.set('value', date.format(inputFormat));
      }
    };

    new Picker.Date(el, Object.merge(options, this.get('date')));
  }
});
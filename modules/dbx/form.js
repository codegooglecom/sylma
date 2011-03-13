/* Document JS */

var sylmaCalendarOptions = {
  months: ['janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'],
  days: ['dimanche', 'lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi'],
  // draggable : false
  'startMonday' : true,
  'format' : '%D %B %Y',
  
  classes: ['i-heart-ny']
  // 'theme' : 'osx-dashboard',
  // 'createHiddenInput' : true,
  // 'hiddenInputFormat' : '%Y-%m-%d'
};

addWindowLoad(function () {
  
  $$('form textarea').each(function(el) {
    
    if (el.get('text') == ' ') el.empty();
  });
});



/* Document JS */

Locale.use('fr-FR');

sylma.dbx = {
  
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
        
        var date = el.get('value') ? Date.parse(el.get('value')) : '';
        var input = el.getNext();
        
        if (date) el.setAttribute('value', date.format(self.dateShowFormat));
        
        var options = {
          
          pickerClass : 'datepicker_jqui',
          format : self.dateShowFormat,
          onSelect: function(date){
            
            input.set('value', date.format(self.dateInputFormat));
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
      // });
    },
    
    getID : function() {
      
      return $('sylma_form_id') ? $('sylma_form_id').get('value') : null;
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
  }),
  
};

Object.append(sylma.dbx, {
  
  files : new Class({
    
    Extends : sylma.classes.layer,
    iCount : 1,
    
    initialize : function(options) {
      
      this.parent(options);
      
      this.loader = this.node.getElements('.sylma-field-loading')[0];
      // this.loader.slide('hide').setStyle('display', 'block');
    },
    
    sendFile : function(oInput) {
      
      var oForm = this.rootObject.node;
      var sAction = oForm.get('action'); // backup action
      
      oInput.blur(); // next time user click it focus come back and load open again
      oForm.set('action', this.rootObject.pathUpload + '.action');
      oForm.set('target', 'sylma-uploader-iframe');
      oForm.submit();
      
      this.loader.fade('in');
      
      // replace old values
      oForm.set('action', sAction);
      oForm.set('target');
      oInput.set('value');
		},
    
    updateFile : function(oFrame) {
      
      var req = new sylma.classes.request();
      var bResult = false;
      
      if (oFrame.contentDocument.childNodes.length) {
        
        var eResult = sylma.importNodes(oFrame.contentDocument.childNodes[0]);
        var sID = req.parseAction(eResult, true);
        
        this.loader.fade('out');
        
        if (sID) {
          
          bResult = true;
          
          this.insert({
            path : this.rootObject.pathUploadView,
            method : 'post',
            arguments : {
              id : sID,
              name : this.parentName + '[' + this.name + ']',
              sylma_form_id : this.rootObject.getID()
            },
            'html' : this.node,
            'html-position' : 'before'
          });
        }
      }
      
      if (!bResult) sylma.sendPopup('Une erreur est survenu, impossible de charger le fichier', 'error');
    }
    
  }),
  
  file : new Class({
    
    Extends : sylma.classes.layer,
    
    askRemove : function() {
      
      sylma.sendConfirm('Voulez-vous supprimer ce fichier ?', this.remove, this);
    },
    
    remove : function() {
      
      this.parent();
    }
  })
});

sylma.dbx.form.implement({
  
  dateShowFormat : '%e%o %B %Y'
});
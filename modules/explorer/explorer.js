/* $Id$ : */

var sExplorerClasses = 'explorer-classes';

var oExplorerClasses = sylma[sExplorerClasses] = {
  
  'explorer' : new Class({
    
    Extends : sylma.classes.layer,
    bUploader : false,
    
    show : function() {
      
      this.mozaic.show();
      this.parent();
    },
    
    hide : function() {
      
      this.mozaic.hide();
      this.parent();
    },
    
    addDirectory : function(oForm) {
      
      var oArguments = {
        'directory' : this.mozaic.getCurrent(),
        'name' : oForm.getElement('input[name=resource-name]').get('value')
      };
      
      var self = this;
      var oRequest = new sylma.classes.request({
        
        'url' : self.pathInterface + '/add-directory.action',
        'data' : oArguments,
        'onSuccess' : function(sResult, oResult) {
          
          this.parseAction(oResult)
          self.mozaic.update();
        }
        
      }).send();
      
      return false;
    },
    
    toggleUploader : function() {
      
      if (!this.bUploader) {
        
        //var iWidth = 600;
        var iWidth = this.node.getSize().x - 20;
        var iHeight = 250;
        
        sylma.load({
          'path' : '/modules/jumploader',
          'method' : 'get',
          'arguments' : 'path=' + this.mozaic.currentDirectory + '&' + iWidth + '&' + iHeight,
          'html' : this.node,
          'onSuccess' : function(oResult) {
            
            var myFx = new Fx.Scroll(window).toElement(oResult);
          }
        });
        
        $('jumploader-caller').set('html', 'Fermer le chargeur');
        
        this.bUploader = true;
        
      } else {
        
        $('jumploader-caller').set('html', 'Ajouter des fichiers');
        $('jumploader').slide('out').get('slide').chain(function() { $('jumploader').dispose(); });
        
        this.bUploader = false;
      }
    }
  }),
  
  'mozaic' : new Class({
    
    Extends : sylma.classes.layer,
    currentDirectory : '',
    
    'initialize' : function(oArgs) {
      
      this.parent(oArgs);
      
      this.updateLightbox();
    },
    
    updateLightbox : function(node) {
      
      node = node || this.node;
      
      var nodes = node.getElements('a.preview-image');
      
      if (nodes.length) {
        
        var box = new CeraBox({
          group: true,
          errorLoadingMessage: 'The requested content cannot be loaded. Please try again later.',
        });
        
        box.addItems(nodes);
      }
    },
    
    getCurrent : function() {
      
      return this.currentDirectory;
    },
    
    update : function(oArguments) {
      
      var self = this;
      
      oArguments = Object.append({path : this.currentDirectory}, oArguments);
      this.currentDirectory = oArguments.path;
      //alert(this.currentDirectory);
      this.parent(oArguments, {
        'onLoad' : function() {
          
          self.updateLightbox();
          self.parentObject.center();
        }});
    }
  }),
  
  'tools' : new Class({
    
    Extends: sylma.classes['menu-common'],
    resource : undefined,
    
    show : function(eTarget) {
      
      if (this.firstShow(eTarget)) {
        
        this.resource = eTarget.retrieve('ref-object');
        this.clearSub(true);
      }
      
      return this.parent(eTarget);
    },
    
    hide : function(bQuick) {
      
      // this.updateRights();
      this.clearSub(bQuick);
      
      return this.parent(bQuick);
    },
    
    isLocked : function() {
      
      for (var i in this.sub) {
        
        if (this.sub[i].isOpen) {
          //sylma.dsp(i);
          return true;
        }
      }
      
      return false;
    },
    
    clearSub : function(bQuick) {
      
      for (var i in this.sub) this.sub[i].hide(bQuick);
    },
    
    loadRights : function() {
      
      // mode
      
      var aMode = {
        'owner' : parseInt(this.resource.mode[0]),
        'group' : parseInt(this.resource.mode[1]),
        'public' : parseInt(this.resource.mode[2])};
      
      var aRights = {
        'read' : SYLMA_MODE_READ,
        'write' : SYLMA_MODE_WRITE,
        'execution' : SYLMA_MODE_EXECUTION};
      
      Object.each(aMode, function(sModeValue, sModeKey) {
        Object.each(aRights, function(sRightValue, sRightKey) {
          
          var eInput = this.sub.rights.node.getElement('input[name=resource-' + sModeKey + '-' + sRightKey + ']');
          
          if (sModeValue & sRightValue) eInput.set('checked', 'checked');
          else eInput.removeProperty('checked');
          
        }, this);
      }, this);
      
      // owner, group
      
      this.sub.rights.node.getElement('input[name=resource-owner]').set('value', this.resource.owner);
      this.sub.rights.node.getElement('input[name=resource-group]').set('value', this.resource.group);
    },
    
    updateRights : function() {
      
      if (this.resource) {
        
        this.hide();
        
        var oResource = this.resource;
        
        var sOwner = this.sub.rights.node.getElement('input[name=resource-owner]').get('value');
        var sGroup = this.sub.rights.node.getElement('input[name=resource-group]').get('value');
        
        var aNodes = this.sub.rights.node.getElements('input[type=checkbox]');
        
        var iMode = 0, iTarget = 0, aMode = [0, 0, 0], aModes = [SYLMA_MODE_READ, SYLMA_MODE_WRITE, SYLMA_MODE_EXECUTION];
        
        aNodes.each(function(eInput) {
          
          if (eInput.get('checked')) aMode[iTarget] |= aModes[iMode];
          
          if (iTarget == 2) {
            
            iMode++;
            iTarget = 0;
            
          } else iTarget++;
          
        });
        
        var sMode = aMode.join('');
        
        if (sOwner != this.resource.owner || sGroup != this.resource.group || sMode != this.resource.mode) {
          
          var oArguments = {
            'resource' : oResource.path,
            'directory' : oResource.isDirectory(),
            'owner' : sOwner,
            'group' : sGroup,
            'mode' : sMode }
          
          var oCaller = this;
          var oRequest = new sylma.classes.request({
            
            'url' : sylma.explorer.pathInterface + oCaller.pathRights + '.action',
            'data' : oArguments,
            'onSuccess' : function(sResult, oResult) {
              
              oCaller.onUpdateRights(this.parseAction(oResult), oResource, oArguments);
            }
          }).send();
        }
      }
      
      return false;
    },
    
    onUpdateRights: function(mResult, oResource, oArguments) {
      
      var bResult = sylma.inttobool(mResult.get('text'));
      
      if (bResult) {
        
        this.hide();
        
        oResource.owner = oArguments.owner;
        oResource.group = oArguments.group;
        oResource.mode = oArguments.mode;
        // oResource.update();
      }
    },
    
    invertRights : function(sName) {
      
      if (sName) sName = '[' + sName + ']';
      else sName = '';
      
      var bChecked, aNodes = this.sub.rights.node.getElements('input' + sName + '[type=checkbox]');
      
      aNodes.each(function(eInput) { if (eInput.get('checked')) bChecked = true; });
      aNodes.each(function(eInput) {
        
        if (bChecked) eInput.removeProperty('checked');
        else eInput.set('checked', 'checked');
      });
      
      return false;
    },
    
    loadName : function() {
      
      var oInput = this.sub.editName.node.getElement('input[name=resource-name]');
      
      oInput.set('value', this.resource.name);
      //oInput.focus();
    },
    
    updateName : function() {
      
      var sName = this.sub.editName.node.getElement('input[name=resource-name]').get('value');
      var oResource = this.resource;
      
      if (sName) {
        
        var oArguments = {
          'resource' : this.resource.path,
          'directory' : this.resource.isDirectory(),
          'name' : sName
        }
        
        var oCaller = this;
        var oRequest = new sylma.classes.request({
          
          'url' : sylma.explorer.pathInterface + oCaller.pathName + '.action',
          'data' : oArguments,
          'onSuccess' : function(sResult, oResult) {
            
            oCaller.onUpdateName(this.parseAction(oResult), oResource, oArguments);
          }
          
        }).send();
      }
      
      return false;
    },
    
    onUpdateName : function(mResult, oResource, oArguments) {
      
      var sPath = mResult.get('text');
      
      if (sPath) {
        
        this.hide();
        oResource.replace(sPath);
      }
    },
    
    deleteResource : function() {
      
      var oResource = this.resource;
      
      var oArguments = {
        'resource' : this.resource.path,
        'directory' : this.resource.isDirectory()
      }
      
      var oCaller = this;
      var oRequest = new sylma.classes.request({
        
        'url' : oCaller.rootObject.pathInterface + oCaller.pathDelete + '.action',
        'data' : oArguments,
        'onSuccess' : function(sResult, oResult) {
          
          oCaller.onDeleteResource(this.parseAction(oResult), oResource);
        }
        
      }).send();
      
      return false;
    },
    
    onDeleteResource : function(mResult, oResource) {
      
      if (sylma.inttobool(mResult.get('text'))) {
        
        this.hide();
        oResource.remove();
      }
    }
    
  }),
  
  'resource' : new Class({
    
    Extends : sylma.classes.layer,
    
    replace : function(sPath) {
      
      var options = {
        'path' : this.getPath(),
        'method' : 'post',
        'arguments' : {
          'resource' : sPath,
          'directory' : sylma.booltostring(this.isDirectory())
        }
      }
      
      if (this.isImage) {
        
        options['onSuccess'] = function(node) {
          
          this.parentObject.updateLightbox(node);
        };
      }
      
      return this.parent(options);
    },
    
    update : function() {
      
      return this.parent({'resource' : this.path});
    }
    
  })
};

function uploaderStatusChanged(uploader) {
  
  var sStatut;
  
  if (uploader.isReady()) {
    
    sylma.explorer.mozaic.update();
    //oExplorer.toogleUploader();
    
    //return window.close();
  }
}

Object.append(oExplorerClasses, {
  
  'file' : new Class({
    
    Extends : oExplorerClasses.resource,
    isImage : false,
    
    isDirectory : function() {
      
      return 0;
    },
  }),
  
  'directory' : new Class({
    
    Extends: oExplorerClasses.resource,
    isDirectory : function() { return 1; },
    
    open : function() {
      
      sylma.explorer.tools.reset();
      
      return this.parentObject.update({'path' : this.path});
    }
  })
  
});


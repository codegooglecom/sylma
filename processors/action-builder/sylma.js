/* Document JS */

var SYLMA_MODE_EXECUTION = 1, SYLMA_MODE_WRITE = 2, SYLMA_MODE_READ = 4;
var SYLMA_USE_CONSOLE = true;
var SYLMA_IS_ADMIN = false;

var sylma = {
  
  classes : {},
  
  defaultMessagesId : false,
  defaultMessagesContainer : false,
  aToBuild : new Array(),
  /* global */
  
  inttobool : function(sValue) {
    
    return parseInt(sValue) === 1 ? true : false;
  },
  
  booltostring : function(bValue) {
    
    return bValue ? 'true' : 'false';
  },
  
  formatDuration : function(iDuration) {
    
    var iHour = Math.floor(iDuration / 3600);
    var iMin = Math.floor((iDuration - (iHour * 3600)) / 60);
    var iSec = Math.floor(iDuration - (iHour * 3600) - (iMin * 60));
    
    var sValue = '';
    
    if (iHour) sValue += iHour + 'h ';
    if (iMin) sValue += iMin + 'mn ';
    sValue += iSec + 's';
    
    return sValue;
  },
  
  trim : function(sValue) {
    
    return sValue ? sValue.replace(/^\s+/g,'').replace(/\s+$/g,'') : '';
  },
  
  /* ajax actions */
  
  importNodes : function(mElements) {
    
    if (typeOf(mElements) == 'array') {
      
      var aResults = new Array;
      
      mElements.each(function(oElement) {
        
        aResults.push(document.importNode(oElement, true));
      });
      
      return aResults;
      
    } else if (mElements) {
      
      if (document.importNode) return window.document.importNode(mElements, true);
      else return mElements;
    }
  },
  
  loadTree : function(sName, sID, oSuccess) {
    
    var self = this;
    
    if (Browser.ie) {
      
      var iVerticalMove = 45;
      
      new Request.HTML({
        'url' : '/sylma/system/ie-warning.xml',
        'onSuccess' : function(sResult, oResult) {
          
          $(document.body).adopt(sResult);
          var node = $('browser-warning');
          
          if (node) {
            
            node.set('morph', {'duration' : 500});
            $('browser-warning').morph({'top' : iVerticalMove, 'opacity' : '0.9', 'width' : '380'});
          }
        }
      }).get(); //&xml-mode=htm
    }
    
    var oResult = new Request.JSON({
      
      'url' : '/index.txt?sylma-result-id=' + sID, 
      'onSuccess' : function(oResult) {
        
        //sylma.dsp(' - DEBUT - ');
        sylma[sName] = sylma.buildRoot(oResult);
        if (oSuccess) oSuccess();
        
        Array.each(self.aToBuild, function(item) { item(); });
        //sylma.dsp(' - FIN - ');
    }}).get();
  },
  
  buildRoot: function(object, sPath, oParent, oRoot) {
    
    var result;
    
    if (!object) this.dsp('Aucun objet reçu pour "' + sPath + '"');
    else {
      for (var i in object) { var bluh; } // TODO ??
      
      //if (!oRoot) oRoot = oParent;
      if (!sPath) sPath = i;
      
      result = this.buildObject(object[i], sPath, oParent, oRoot);
    }
    
    return result;
  },
  
  buildObject: function(object, sPath, parentLayer, rootObject, iDepth) {
    // sylma.dsp(this.aToBuild.length);
    var sKey, sName, oSub, bRoot, eNode, isRoot;
    var oResult = {};
    var bResult = true;
    
    if (!rootObject) isRoot = true;
    else isRoot = false;
    
    if (object['init']) {
      
      var sClass = null;
      
      if (parentLayer) sClassBase = parentLayer['sylma-classbase'];
      else sClassBase = '';
      
      if (!parentLayer) parentLayer = oResult;
      
      if (!iDepth) iDepth = 0;
      else if (iDepth > 10) {
        
        this.dsp('Trop de récursion !');
        return;
      }
      
      if (object['init']['extend-base']) {
        
        var sClassBase = object['init']['extend-base'];
        if (!sClassBase) this.dsp('Classe de base vide !');
      }
      
      if (object['init']['extend-class']) {
        
        var sClassName = object['init']['extend-class'];
        bRoot = sClassName[0] == '/';
        
        var bHidden = object['init']['hidden'] ? true : false;
        
        var sArgs = "({'object' : object, 'parent' : parentLayer, 'base' : sClassBase, 'path' : sPath, 'root' : rootObject, 'hidden' : bHidden})"
        
        if (bRoot || !sClassBase) {
          
          if (bRoot) sClassName = sClassName.substr(1);
          sClassName = sClassName[0] == '[' ? sClassName : '.' + sClassName;
          
          try { eval('oResult = new window' + sClassName + sArgs); }
          catch(e) { this.dsp('Nom de classe introuvable : window' + sClassName + '<br/>' + e); bResult = false; };
          
        } else {
          
          try { eval('oResult = new window' + sClassBase + sClassName + sArgs); }
          catch(e) {
            
            this.dsp("Classe '" + sClassName + "' introuvable ou invalide dans la classe de base window'" + sClassBase + "'<br/>" + e); 
            bResult = false;
          };
        }
      }
      
      if (!bResult) this.dsp('Erreur :: Impossible de créer l\'objet');
      else if (!rootObject) rootObject = oResult;
    }
    
    if (bResult) {
      
      // if (isRoot && oResult.node) oResult.node.setStyle('opacity', 0.25);
      // if (isRoot) alert(oResult.node);
      if (sClassBase) oResult['sylma-classbase'] = sClassBase;
      
      if (object['properties'] && object['properties'].length != 0) {
        
        var sType;
        
        for (sKey in object['properties']) {
          
          oSub = object['properties'][sKey];
          sType = typeOf(oSub);
          
          if (sType == 'object') { // JS Object
            
            if (oSub['is-sylma-object']) oResult[sKey] = this.buildObject(oSub, sKey, oResult, rootObject, iDepth + 1); // Sylma object
            else if (oSub['is-sylma-array']) { // Sylma array
              
              delete(oSub['is-sylma-array']);
              oResult[sKey] = new Array();
              
              for (var sSubKey in oSub) {
                
                sPath = sKey + '[\'' + sSubKey + '\']';
                oResult[sKey][sSubKey] = this.buildObject(oSub[sSubKey], sPath, oResult, rootObject, iDepth + 1);
              }
              
            } else if (oSub['is-sylma-property']) { // Sylma others
              
              delete(oSub['is-sylma-property']);
              oResult[sKey] = new Object;
              
              for (var sSubKey in oSub) {
                
                sPath = sKey + '.' + sSubKey;
                oResult[sKey][sSubKey] = this.buildObject(oSub[sSubKey], sPath, oResult, rootObject, iDepth + 1);
              }
              
            } else { // Sylma others
              
              this.dsp('Type d\'object inconnu : ' + sKey);
              this.dsp(this.view(object['properties']));
            }
            
          } else if (sType == 'string') oResult[sKey] = oSub;// JS String
          else this.dsp('Type \'' + sType + '\' inconnu dans ' + sKey + ' !'); // JS Others
        }
      }
      //sylma.dsp(sPath);
      if (object['methods']) this.buildMethods(object, oResult);
      if (oResult.onBuilt) this.aToBuild.push(oResult.onBuilt.bind(oResult));
      
      if (isRoot  && oResult.node) this.enableNode(oResult.node);
      
      return oResult;
      
    }
    
    return false;
  },
  
  buildEvent : function(method, sMethod, eNode, oParent) {
    
    var oBound;
    
    if (method.limit) {
      
      var sLimit = method.limit;
      
      eNode.addEvent(method.name, function(e) {
        
        oBound = sylma.limitFunc.bind(eNode);
        oBound(e, sMethod, sLimit);
      });
    }
    else if (method.delay) {
      
      eNode.addEvent(method.name, function(e) {
        
        var oBound = sylma.delayFunc.bind(eNode);
        oBound(e, sMethod, method.timer, parseInt(method.delay), oParent);
      });
    }
    else if (method.name == 'keydown' && method.key) {
      
      eNode.addEvent(method.name, function(e) {
        
        var oBound = sylma.keyDownFunc.bind(eNode);
        oBound(e, sMethod, method.key);
      });
      
    }
    else eNode.addEvent(method.name, sylma.methods[sMethod]); // add event
  },
  
  buildMethods: function(object, oParent) {
    
    var method, eNode;
    var sLimit, oBound;
    
    for (var sMethod in object.methods) {
      
      method = object.methods[sMethod];
      
      if (!sylma.methods) sylma.dsp('Liste des méthodes introuvable');
      else {
        
        if (sylma.methods[sMethod]) {
          
          if (method.event) {
            
            //event
            
            if (method.name && (method['path-node'] || method['id-node'])) {
              
              // get target node
              
              if (method['path-node']) {
                
                eNode = $$(method['path-node']);
                if (eNode.length) eNode = eNode[0];
              }
              else eNode = $(method['id-node']);
              
              if (typeOf(eNode) == 'element') {
                
                eNode.store('ref-object', oParent); // store parent object in node
                this.buildEvent(method, sMethod, eNode, oParent);
              }
              else {
                
                this.dsp('Erreur :: Objet DOM introuvable - path : "' + method['path-node'] + '" - id : ' + method['id-node']);
              }
              
            } else {
              
              this.dsp("Erreur :: Méthode '" + sMethod + "' invalide !");
            }
          }
          else { // method
            
            oParent[method.name] = sylma.methods[sMethod];
          }
        }
        else {
          
          this.dsp("Erreur :: Méthode '" + sMethod + "' introuvable !");
        }
      }
    }
  },
  
  delayFunc : function(e, sMethod, sTimer, iDelay, oParent) {
    
    if (!oParent.timer) oParent.timer = [];
    oParent.timer[sTimer] = sylma.methods[sMethod].delay(iDelay, this, e);
    
    return true;
  },
  
  limitFunc : function(e, sMethod, sTargets) {
    
    var oTarget;
    var bResult = false;
    var aTargets = sTargets.split(',');
    
    for (var i = 0; i < aTargets.length; i++) {
      
      var sPath = aTargets[i].replace(/^\s+/g,'').replace(/\s+$/g,'');
      
      if (sPath[0] == '$') {
        
        if (sPath[1] == '>') var aChildren = this.getChildren(sPath.substring(2));
        else var aChildren = this.getElements(sPath.substring(1));
        
        aChildren.each(function(eNode) {
          
          if (!bResult && eNode === e.target) bResult = true;
        });
        
        if (bResult) break;
        
      } else {
        
        switch (sPath) {
          
          case 'self' : oTarget = this; break;
          case 'first' : oTarget = this.getFirst(); break;
        }
        
        if (e.target === oTarget) {
          
          bResult = true;
          break;
        }
      }
    }
    
    if (bResult) {
      
      var oBounded = sylma.methods[sMethod].bind(this, e);
      return oBounded();
      
    } else return true;
  },
  
  keyDownFunc : function(e, sMethod, sKeys) {
    
    var aKeys = sKeys.split(',');
    var aResults = new Array();
    // sylma.dsp(e.code);
    for (var i = 0; i < aKeys.length; i++) {
      // alert(aKeys[i]);
      switch (aKeys[i]) {
        
        case 'Enter' : iKey = 13; break;
        case 'Esc' : iKey = 27; break;
        case 'Tab' : iKey = 9; break;
        default : iKey = aKeys[i];
      }
      
      aResults.push(iKey);
    }
    //sylma.dsp(e.code + ' - ' + aResults.contains(e.code));
    if (e.code && aResults.contains(e.code)) {
      
      var oBounded = sylma.methods[sMethod].bind(this, e);
      return oBounded();
      
    } else return true;
  },
  
  createXML : function(xml, parent) {
    
    parent = parent || document;
    
    switch (xml.nodeType) {
      
      case  1 : // Element type
        
        var tmp;
        var oElement = new Element(xml.nodeName);
        
        for(var j = 0; j < xml.attributes.length; j++) {
          
          tmp = xml.attributes[j];
          oElement.setProperty(tmp.nodeName, tmp.nodeValue);
        }
        
        if (xml.childNodes.length)
          for(var i = 0; i < xml.childNodes.length; i++)
            this.createXML(xml.childNodes[i], oElement);
        
        if (parent) {
          //alert(parent.ownerDocument);
          //oElement = parent.ownerDocument.importNode(oElement, true);
          parent.adopt(oElement);
        }
        
        return oElement;
        
      break;
      
      case 3 :
        
        parent.set({'text' : xml.nodeValue});
        
      break;
      
      case 9 : break;
      case 7 : break; // version="1.0" encoding="utf-8"
      case 8 : break // doctype
      
      default : sylma.dsp('Impossible d\'ajouter ' + xml.nodeValue + ' de type ' + xml.nodeType);
    }
    
    return parent;
  },
  
  disableNode : function(node, bFast) {
    
    if (node) {
      
      if (bFast) node.setStyle('opacity', 0.1);
      else {
        
        node.get('tween').set('duration', 'short');
        node.tween('opacity', 0.1);
      }
    }
  },
  
  enableNode : function(node, iOpacity) {
    
    iOpacity = iOpacity || 1;
    
    if (node) {
      
      if (node.hasClass('sylma-loading')) {
        
        var iFrom = node.getStyle('opacity');
        node.setStyle('opacity', iFrom);
        
        node.removeClass('sylma-loading')
      }
      
      node.get('tween').set('duration', 100);
      node.tween('opacity', iOpacity);
    }
  },
  
  load : function(oOptions) {
    
    if (!oOptions.method) oOptions.method = 'get';
    
    var sPath = oOptions.path;
    var self = this;
    
    var sWindow = oOptions.window ? oOptions.window : 'action';
    
    this.request = new this.classes.request({
      
      'url' : sPath + '.' + sWindow,
      'data' : oOptions.arguments,
      'method' : oOptions.method,
      'onSuccess' : function(sResult, oXML) {
        
        if (Browser.ie) {
          
          //oXML = { documentElement: self.createXML(oXML.documentElement) }; // not working yet
          // oXML = self.createXML(oXML); // not working yet
          oXML = $(oXML);
          //oXML = oXML.ownerDocument;
        }
        
        var oContentContainer = this.parseAction(oXML);
        
        if (!oContentContainer) {
          
          sylma.dsp('Réponse du serveur illisible', 'error');
        }
        else {
          
          var oContent = self.importNodes(oContentContainer.getFirst());
          
          if (!oContent) {
            
            sylma.sendPopup('Erreur dans la réponse ou session expirée', 'error');
          }
          else {
            
            self.disableNode(oContent, true);
            
            if (oOptions.replace) oContent.replaces(oOptions.html);
            else {
              
              if (oOptions['html-position']) oContent.inject(oOptions.html, oOptions['html-position']);
              else oOptions.html.grab(oContent);
            }
            
            if (oOptions.onLoad) oOptions.onLoad();
            
            if (oOptions.position == 'center') {
              
              var iLeft = (window.getSize().x - oContent.getSize().x) / 2;
              var iTop = ($(window).getSize().y - oContent.getSize().y) / 2;
              
              oContent.setStyles({'left' : iLeft, 'top' : iTop});
            }
          }
          
          var iOpacity;
          
          // TODO kill old layer
          //layer.node.destroy(); 
          
          var sRecall = oContentContainer.getProperty('recall');
          
          if (sRecall) { // get new object
            
            var sMethods = oContentContainer.getProperty('methods');
            
            if (sMethods) { // has methods, first load them
              
              var oMethods = new Request({
                
                url : '/index.txt?sylma-result-id=' + sMethods,
                onFailure : function() { sylma.sendPopup('Réponse du serveur illisible', 'error'); },
                onSuccess : function(sResponse) {
                  
                  eval(sResponse);
                  self.replace(sRecall, oOptions, oContent);
                  
                  if (oContent) self.enableNode(oContent);
                  
              }}).get();
            }
            else { // no methods
              
              self.replace(sRecall, oOptions);
              self.enableNode(oContent, iOpacity);
              
              if (oOptions.onSuccess) oOptions.onSuccess(oContent);
            }
          }
          else {
            
            self.enableNode(oContent, iOpacity); // only change content node
            if (oOptions.onSuccess) oOptions.onSuccess(oContent);
            //oCaller.node = oContent;
          }
        }
      }
    }).send();
    
    return true;
  },
  
  replace : function(sID, oOptions, oResult) {
    
    var self = this;
    
    var oJSON = new Request.JSON({
      
      'url' : '/index.txt?sylma-result-id=' + sID, 
      'onSuccess' : function(oResponse) {
        
        if (!oOptions.parent) oOptions.parent = sylma;
        
        self.aToBuild = new Array();
        
        var oNewObject = self.buildRoot(oResponse, oOptions.name, oOptions.parent, oOptions.root);
        // sylma.dsp(this.aToBuild.length);
        if (oOptions.position) oNewObject['sylma-position'] = oOptions.position;
        // sylma.dsp(oOptions.parent['sylma-path'] + ' / ' + oOptions.name);
        if (oOptions['old-name']) eval('delete(oOptions.parent.' + oOptions['old-name'] + ')'); // delete old object
        if (oNewObject) eval('oOptions.parent.' + oOptions.name + ' = oNewObject'); // insert new object
        // sylma.dsp('oOptions.parent.' + oOptions.name + ' = oNewObject');
        
        // sylma.dsp(self.aToBuild.length);
        Array.each(self.aToBuild, function(item) { item(); });
        
        oOptions.resultObject = oNewObject;
        
        // at last : onSuccess function
        if (oOptions.onSuccess) oOptions.onSuccess(oNewObject);
        
    }}).get();
  },
  
  'methods' : {},
  
  /* Window */
  
  center : function(node, bAbsolute) {
    
    if (bAbsolute) {
      
      node.position();
    }
    else {
      
      node.position({ignoreScroll: true});
      node.setStyle('position', 'fixed');
    }
  },
  
  sendPopup : function(sMessage, sStatut, callback, caller) {
    
    sStatut = sStatut | 'notice';
    
    var oMessage = new Element('div', {
      'class' : 'sylma-popup sylma-message-' + sStatut,
      html : '<div>' + sMessage + '</div>',
      styles : {
        opacity : '0'
      }
    });
    
    oMessage.adopt(
      new Element('input', {
        type: 'button',
        value: 'Ok',
        events : {
          'click' : function() {
            
            oMessage.fade('out').get('tween').chain(function() { oMessage.dispose(); });
            
            if (callback) {
              
              var bound = callback.bind(caller);
              bound();
            }
          }}
      }));
    
    $(document.body).grab(oMessage);
    this.center(oMessage);
    
    oMessage.fade('in');
    
    return false;
  },
  
  sendConfirm : function(sMessage, callback, caller) {
    
    var oMessage = new Element('div', {
      'class' : 'sylma-popup sylma-message-confirm',
      html : '<div>' + sMessage + '</div>',
      styles : {
        opacity : '0'
      }
    });
    
    oMessage.adopt(
      new Element('input', {
        type: 'button',
        value: 'Oui',
        events : {
          'click' : function() {
            
            oMessage.fade('out').get('tween').chain(function() { oMessage.dispose(); });
            
            if (callback) {
              
              var bound = callback.bind(caller);
              bound();
            }
          }}
      }),
      new Element('input', {
        type: 'button',
        value: 'Non',
        events : {
          'click' : function() {
            
            oMessage.fade('out').get('tween').chain(function() { oMessage.dispose(); });
          }}
      }));
    
    document.body.grab(oMessage);
    this.center(oMessage);
    
    oMessage.fade('in');
    
    return false;
  },
  
  removeElement : function(el) {
    
    el.fade('out').get('tween').chain(function() { el.dispose(); });
  },
  
  /* Utils */
  
  dsp : function(sContent, sStatut) {
    
    if (SYLMA_IS_ADMIN) {
      
      if (SYLMA_USE_CONSOLE) console.log(sContent);
      else this.sendPopup(sContent, sStatut);
    }
  },
  
  dspf : function(obj) {
    
    this.dsp(this.view(obj));
  },
  
  view : function(obj, parent, recursion) {
    
    if (!recursion) recursion = 0;
    
    var sContent = '';
    // var iMaxRecursion = 10;
    
    for (var i in obj) {
      
      try {
        
        sContent += '<div style="margin-left: ' + (6 - recursion) + 'em;">';
        
        // if (parent) sContent = parent + "." + i + " : " + obj[i];
        sContent += '<strong>' + i + '</strong>' + " : " + obj[i];
        
        if (typeof obj[i] == "object" && recursion) {
          
          sContent += '<div style="margin-left: ' + (6 - recursion + 1) + 'em">';
          
          // if (parent) sContent += this.view(obj[i], parent + "." + i, recursion - 1);
          if (recursion) sContent += this.view(obj[i], i, recursion - 1);
          
          sContent += '</div>';
        }
        
        sContent += '</div>';
        
      } catch (t) { sContent += '<br/>Erreur :: Propriété : ' + i + ' (' + t + ')<br/>'; }
    }
    
    return sContent;
    //this.dsp(sContent);
  }

};

sylma.classes.Base = new Class({
  
  initialize : function(oArgs) {
    
    this['sylma-classbase'] = oArgs['base'];
    
    // Add default properties
    
    if (oArgs['object']['init']['id-node']) { // Attach reference node
      
      var eNode = $(oArgs['object']['init']['id-node']);
      
      if (eNode) {
        //sylma.dsp(oArgs['path'] + ' : ' + oArgs['parent']['sylma-path'] + ' / ' + eNode.get('class'));
        this.node =  eNode;
        eNode.store('ref-object', this);
        
      } else sylma.dsp("Erreur : Element lié à l'objet '" + oArgs['object']['init']['id-node'] + "' introuvable !");
    }
    
    //sylma.dsp(oArgs['path'] + ' : ' + oArgs['parent']['sylma-path']);
    
    this.parentObject = oArgs['parent']; // Attach parent object
    this['sylma-path'] = oArgs['path']; // Attach parent object ref
    this.rootObject = oArgs['root']; // Attach root object (layout)
  },
  
  getName : function() {
    
    return this['sylma-path'];
  },
  
  setTimer : function(sName, callback, iTime, el) {
    
    el = el || this;
    
    if (el) var bound = callback.bind(el);
    else bound = callback;
    
    if (!this.timer) this.timer = new Array();
    
    this.timer[sName] = window.setInterval(bound, iTime);
  },
  
  clearTimer : function(sName) {
    
    if (this.timer) {
      
      window.clearInterval(this.timer[sName]);
      this.timer[sName] = undefined;
    }
  },
  
  remove : function() {
    
    if (this.timer) this.timer.each(function(item, sKey) { this.clearTimer(sKey); });
  }

});

sylma.classes.request = new Class({
  
  Extends : Request,
  
  cleanStack : function(oContainer, iMaxLength) {
    
    var self = this;
    
    if (!iMaxLength) var iMaxLength = 5;
    
    var iLength = oContainer.getChildren().length;
    var oKilled = oContainer.getFirst().getNext();
    
    if (iLength > iMaxLength) {
      
      oKilled.set('tween', {onComplete : function() { oKilled.destroy(); self.cleanStack(oContainer, iMaxLength); }});
      oKilled.tween('opacity', 0);
    }
  },
  
  'parseAction' : function(oResult, bText) {
    
    var oContainer = $('msg-admin');
    // sylma.dsp(oResult.childNodes[0].tagName);
    //if (!$(oResult)) {sylma.dsp(typeOf(oResult));sylma.dsp(bText);}
    
    if (!oResult) {
      
      sylma.sendPopup('Erreur ! L\'appel a échoué', 'error');
    }
    else {
      
      if (typeOf(oResult) != 'element') oResult = oResult.firstChild;
      //oResult = 
      //sylma.dsp(typeOf(oResult));
      
      
      if (Browser.ie) {
        
        oResult = sylma.createXML(oResult);
        // sylma.dsp(typeOf(oResult));
        // var oContent = oResult.('content')[0];
      }
      
      var oMessages = oResult.getElement('messages');
      var oContent = oResult.getElement('content');
      var oInfos = oResult.getElement('infos');
      
      if (oContainer && oInfos) oContainer.adopt(sylma.importNodes(oInfos.getFirst()), 'top');
      
      var sMessages = sylma.defaultMessagesId;
      // if (!oTarget) oTarget = sylma.defaultMessagesContainer;
      
      if (oMessages && oMessages.getChildren().length) {
        
        var oMessagesContent = oMessages.getFirst();
        
        if (oMessagesContent) {
          
          oMessagesContent = sylma.importNodes(oMessagesContent);
          
          //oMessagesContent.fade('hide');
          
          oMessagesContent.addClass('messages-float');
          if (oContainer) oContainer.adopt(oMessagesContent, 'top');
          
          var pf = new PulseFade(oMessagesContent, {'times':  6, 'duration':  600, 'max' : 1, 'min' : 0.2}); //
          pf.start();
          
          //oMessagesContent.fade('in').;
        }
      }
      
      if (oContainer) this.cleanStack(oContainer, 8);
      
      if (bText && oContent) return oContent.get('text');
      else return oContent;
    }
  }
});

sylma.classes.layer = new Class({
  
  Extends : sylma.classes.Base,
  isOpen : true,
  timer : undefined,
  
  initialize : function(oArgs) {
    
    if (oArgs['hidden']) this.isOpen = false;
    this.parent(oArgs);
  },
  
  getPath : function() {
    
    var sPath = this['sylma-update-path'];
    
    if (this['sylma-update-origin']) {
      
      switch (this['sylma-update-origin']) {
        
        case 'interface' : sPath = this.rootObject.pathInterface + sPath; break;
        case 'action' : sPath = this.rootObject.path + sPath; break;
      }
    }
    
    return sPath;
  },
  
  insert : function(oOptions) {
    
    oOptions = Object.append({
      'html': this.node,
      'parent' : this,
      'root' : this.rootObject,
      'path' : this.getPath()
      
    }, oOptions);
    
    return sylma.load(oOptions);
  },
  
  center : function() {
    
    var iLeft = ($(window).getSize().x - this.node.getSize().x) / 2;
    var iTop = ($(window).getSize().y - this.node.getSize().y) / 2;
    
    //this.node.setStyle('top', iTop);
    
    iTop = iTop >= 0 ? iTop : 0;
    
    var oFX = new Fx.Morph(this.node, {'transition' : 'sine:in:out' });
    
    oFX.start({
      'top': iTop,
      'left': iLeft
    });
    
    // this.node.tween('left', iLeft);
    //.setStyles({'left' : iLeft, 'top' : iTop});
  },
  
  replace : function(options, target) {
    
    options = Object.append({
      
      'html' : this.node,
      'old-name' : this['sylma-path'], // optional
      'name' : this['sylma-path'], // temp : replace js + html
      'parent' : this.parentObject, // optional
      'root' : this.rootObject, // optional
      'replace' : true
    }, options);
    
    if (options.html) sylma.disableNode(options.html);
    
    if (this['sylma-send-method']) options.method = this['sylma-send-method'];
    if (this['sylma-position']) options.position = this['sylma-position'];
    
    return sylma.load(options);
  },
  
  update : function(oArguments, oOptions) {
    
    if (!oOptions) oOptions = {};
    
    oOptions = Object.append({
      'path' : this.getPath(),
      'arguments' : oArguments
    }, oOptions);
    
    return this.replace(oOptions);
  },
  
  remove : function() {
    
    this.parent();
    
    var oElement = this.node;
    
    oElement.fade('out').get('tween').chain(function() { oElement.dispose(); });
    //sylma.sendPopup('delete(this.parentObject["' + this['sylma-path'] + '"])');
    eval('delete(this.parentObject["' + this['sylma-path'] + '"])');
  },
  
  show : function() {
    
    if (!this.isOpen) {
      
      this.node.fade('in');
      this.isOpen = true;
    }
    
    return true;
  },
  
  hide : function(bQuick) {
    
    if (bQuick) {
      
      this.node.get('tween').cancel();
      this.node.fade('hide');
      
    } else if (this.isOpen) {
      
      this.node.fade('out');
       //sylma.dsp('[hide] ' + this.node.id);
    }
    
    this.isOpen = false;
    
    return true;
  }
  
});
  
sylma.classes.menu = new Class({
  
  Extends : sylma.classes.layer,
  isOpen : false,
  
  valueOf : function() {
    
    return '[obj] ' + this.node + ' #' + this.node.id;
  }
});

sylma.classes.menuAlert = new Class({
  
  Extends : sylma.classes.layer,
  overlay : null,
  
  initialize : function(options) {
    
    this.parent(options);
    this.node.addClass('sylma-popup');
  },
  
  show : function() {
    
    this.overlay = new Element('div', {
      'class' : 'sylma-overlay-document'
    });
    
    $(document.body).setStyles({'height' : $(window).getSize().y + 'px', 'overflow' : 'hidden'});
    
    this.overlay.setStyle('opacity', 0);
    $(document.body).grab(this.overlay);
    this.overlay.fade(0.8);
    
    this.node.setStyles({
      visibility: 'visible',
      opacity: 0.1
    });
    
    this.center();
    this.parent();
  },
  
  hide : function() {
    
    $(document.body).setStyles({'height' : 'auto', 'overflow' : 'auto'});
    this.overlay.fade('out');
    this.parent();
  },
});

sylma.classes['menu-common'] = new Class({
  
  Extends : sylma.classes.menu,
  parentNode : undefined,
  originNode : undefined,
  
  'show' : function(eTarget) {
    
    if (this.firstShow(eTarget)) {
      
      if (!this.originNode) this.originNode = this.node.getParent();
      
      this.hide(true);
      
      $(eTarget).grab(this.node, 'top');
      
      this.parentNode = eTarget;
    }
    
    return this.parent();
  },
  
  'resetParent' : function() {
    
    this.parentNode = undefined;
  },
  
  'firstShow' : function(eTarget) {
    
    return (this.parentNode !== eTarget);
  },
  
  'reset' : function() {
    
    this.hide(true);
    
    this.resetParent();
    if (this.originNode) this.originNode.grab(this.node);
    if (!this.node.getChildren().length) sylma.dsp('tools perdus [a] !');
  }
});

var PulseFade = new Class({
			
	//implements
	Implements: [Options,Events],

	//options
	options: {
		min: 0,
		max: 1,
		duration: 200,
		times: 5
	},
	
	//initialization
	initialize: function(el,options) {
		//set options
		this.setOptions(options);
		this.element = $(el);
		this.times = 0;
	},
	
	//starts the pulse fade
	start: function(times) {
		if(!times) times = this.options.times * 2;
		this.running = 1;
		this.fireEvent('start').run(times -1);
	},
	
	//stops the pulse fade
	stop: function() {
		this.running = 0;
		this.fireEvent('stop');
	},
	
	//runs the shizzle
	run: function(times) {
		//make it happen
		var self = this;
		var to = self.element.get('opacity') == self.options.min ? self.options.max : self.options.min;
		self.fx = new Fx.Tween(self.element,{
			duration: self.options.duration / 2,
			onComplete: function() {
				self.fireEvent('tick');
				if(self.running && times)
				{
					self.run(times-1);
				}
				else
				{
					self.fireEvent('complete');
				}
			}
		}).start('opacity',to);
	}
});

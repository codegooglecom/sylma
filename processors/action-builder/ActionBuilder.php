<?php

define('SYLMA_PATH_ACTIONBUILDER', '/sylma/processors/action-builder');
define('SYLMA_NS_ACTIONBUILDER', 'http://www.sylma.org/processors/action-builder');

class ActionBuilder extends XML_Processor  {
  
  private $oMethods;
  private $aNS = array('la' => SYLMA_NS_ACTIONBUILDER);
  private $bFirstPass = true;
  
  private $sExtend = 'extend-class';
  
  public function __construct() {
    
    if (Controler::getWindowSettings()->testAttribute('ajax', true)) {  // TODO : temp & ugly
      
      $this->oMethods = new XML_Document(new XML_Element('la:root', null, null, SYLMA_NS_ACTIONBUILDER));
      
    } else $this->bFirstPass = false;
  }
  
  public function isFirstPass() {
    
    return $this->bFirstPass;
  }
  
  public function getNS() {
    
    return $this->aNS;
  }
  
  public function onElement($oElement, XML_Action $oAction) {
    
    $mResult = null;
    
    // ONLY layer's current action
    
    if (in_array($oElement->getName(true), array('layout', 'layer'))) {
      
      if (!$oElement->get("la:property[@name='sylma-update-path']", $this->aNS)) {
        
        $sPath = $this->getAction()->getPath()->getActionPath();
        $oElement->addNode('property', $sPath, array('name' => 'sylma-update-path'), SYLMA_NS_ACTIONBUILDER);
      }
    }
    
    // not root objects
    
    switch ($oElement->getName()) {
      
      case 'replace-events' : // replace events before parsing for better performance
        
        $oDocument = new XML_Document(new XML_Element('root', $this->buildChildren($oElement)));
        $this->replaceMethodsDefault($oDocument, $oAction);
        
        $mResult = $oDocument->getChildren();
        
      break;
      
      case 'method' : 
        
        if (!$oElement->getId()) $mResult = $this->buildMethod($oElement, $oAction);
        
      break;
      
      case 'event' :
        
        $mResult = $oElement->replace($this->buildMethod($oElement, $oAction));
        
      break;
      
      default :
        
        if ($this->isFirst()) { // root object
          
          $oElement->set($this->buildChildren($oElement));
          
          $mResult = new XML_Document(new HTML_Div($oElement));
          
          $this->buildResult($this->parseAll($mResult, $oAction));
          
          $mResult = $mResult->getChildren();
          
        } else {
          
          
          $oElement->set($this->buildChildren($oElement));
          $mResult = $oElement;
        }
        
      break;
    }
    
    return $mResult;
  }

  private function replaceMethodsDefault($oDocument, $oAction) {
    
    $this->replaceMethods($oDocument->query('//la:event | //la:method', $this->aNS), $oAction);
  }
  
  private function replaceMethods($oMethods, $oAction) {
    //$aMethods = array('event', 'click', 'dblclick', 'mousedown', 'mouseup', 'mouseover', 'mouseout', 'mousemove', 'key-down');
    
    foreach ($oMethods as $oMethod) {
      
      $oMethod->replace($this->buildMethod($oMethod, $oAction));
    }
  }
  
  /**
   * Replace methods with unique id
   */
  
  private function buildMethod($oMethod, $oAction) {
    
    $sName = $oMethod->getAttribute('name');
    
    if (!$sSource = $oMethod->getAttribute('file-source')) $sSource = $oAction->getPath();
    
    $sChildId = 'method-'.sprintf('%u', crc32($sSource.$sName.$oMethod->read()));
    //$sChildId = 'method-'.bin2hex(substr(md5($oAction->getPath().$sName.$iCount), 0, 7));
    //$sChildId = 'method-'.bin2hex(substr(md5($sSource.$sName.$iCount), 0, 7));
    
    if (!$oResult = $this->oMethods->get("//*[@id = '$sChildId']", $this->getNS())) {
      
      $oResult = new XML_Element($sName, null, array('id' => $sChildId));
      
      if ($this->isFirstPass()) {
        
        $sContent = $oMethod->getValue();
        
        $aReplaces = array(
          '/%([\w-_]+)%/'         => '\$(this).retrieve(\'$1\')',
          '/%([\w-_]+)\s*,\s*([^%]+)%/'  => '\$(this).store(\'$1\', $2)');
        
        $oResult->add(preg_replace(array_keys($aReplaces), $aReplaces, $sContent)."\n");
        
        /*
        $aDatas = array();
        $sContent = '';
        
        if ($oDatas = $oMethod->query('la:data', $this->aNS)) {
          
          foreach ($oDatas as $oData) {
            
            $aDatas[$oData->getAttribute('name')] = ($oData->getAttribute('format') == 'object') ? $oData->getValue() : addQuote($oData->getValue());
            $oData->remove();
          }
        }
        
        foreach ($aDatas as $sKey => $sValue) $sContent .= "%$sKey, $sValue%;\n";
        */
        
        $this->oMethods->add($oResult);
      }
    }
    
    $oNewMethod = new XML_Element('la:method', null, array(
      'id' => $sChildId,
      'name' => $sName,
      'extract-ref' => 'parent'), SYLMA_NS_ACTIONBUILDER);
    
    $oNewMethod->cloneAttributes($oMethod, array('delay', 'timer', 'limit', 'key'));
    if ($oMethod->getName() == 'event') $oNewMethod->setAttribute('event', 1);
    
    return $oNewMethod;
  }
  
  /**
   * Adapt objects and methods for last parsing to js object.
   */
  
  private function parseAll($oDocument, $oAction) {
    
    $this->parseObjects($oDocument->query("//la:layout | //la:layer | //la:object", $this->aNS), $oAction);
    $this->parseMethods($oDocument->query("//la:method | //la:event", $this->aNS), $oAction);
    
    return $oDocument->extractNS(SYLMA_NS_ACTIONBUILDER, false); // Extract action tree
  }
  
  /**
   * clone some attributes, define class, build references and set the name
   */
   
  private function parseObjects($oElements, $oAction) {
    
    $bFirst = true;
    
    foreach ($oElements as $oElement) {
      
      $sName = uniqid('object-');
      
      // Attributes replacements
      
      $aReplacements = array('class' => $this->sExtend);
      
      foreach ($aReplacements as $sAttribute => $sNewAttribute) {
        
        if ($sValue = $oElement->getAttribute($sAttribute)) {
          
          $oElement->setAttribute($sNewAttribute, $sValue);
          $oElement->setAttribute($sAttribute);
        }
      }
      
      // Parsing base and class extend's name
      
      $aReplacements = array('extend-class', 'extend-base');
      
      foreach ($aReplacements as $sAttribute) {
        
        if ($sClassBase = $oElement->getAttribute($sAttribute)) {
          
          if ($sClassBase[0] == '/') {
            
            $sClassBase = substr($sClassBase, 1);
            $sClassResult = '/';
            
          } else $sClassResult = '';
          
          foreach (explode('.', $sClassBase) as $sClass) $sClassResult .= '['.addQuote($sClass).']';
          $oElement->setAttribute($sAttribute, $sClassResult);
        }
      }
      
      if ($oElement->countChildren() == 1 && $oElement->testAttribute('hidden', false)) {
        
        $oElement->getFirst()->pushAttribute('style', 'visibility: hidden; opacity: 0');
        // $oElement->shift(new XML_Element('la:property', 'true', array('name' => 'isOpen'), SYLMA_NS_ACTIONBUILDER));
      }
      
      // ref node : html node visible entity of the object
      
      if ($oElement->testAttribute('ref', true)) {
        
        if ($oRefNode = $this->buildReference($oElement)) {
          
          if (!$sId = $oRefNode->getId()) $oRefNode->setAttribute('id', $sName);
          else $sName = $sId;
          
          if ($bFirst) $oRefNode->addClass('sylma-loading');
          
        } else dspm(xt('Noeud HTML de référence non trouvé pour %s', view($oElement, false)), 'action/error');
        
        $oElement->setAttribute('id-node', $sName);
        
        // name
        
        if (($oElement->getParent() && ($oElement->getParent()->getName(true) != 'group')) && (!$oElement->getAttribute('name'))) {
          
          $oElement->setAttribute('name', $sName);
        }
      }
      
      $bFirst = false;
    }
  }
  
  /**
   * build references and set path to node
   */
  
  private function parseMethods($oElements, $oAction) {
    
    $oPreviousParent = null;
    
    foreach ($oElements as $oElement) {
      
      if (!$oElement->getId()) $oElement = $oElement->replace($this->buildMethod($oElement, $oAction));
      
      if ($oElement->hasAttribute('event') && ($oRefNode = $this->buildReference($oElement))) {
        
        if ($sRefId = $oRefNode->getId()) { // refNode has ID, just hook
          
          $oElement->setAttribute('id-node', $sRefId);
          
        } else { // refNode has no ID, get parent first ID'ed
          if ($oElement->getParent() !== $oPreviousParent) { // if methods are siblings
            
            $oParent = $oElement;
            do {
              $oParent = $oParent->getParent(SYLMA_NS_ACTIONBUILDER);
              
            } while ($oParent && !$oParent->hasAttribute('id-node'));
            
            if (!$oParent) dspm(xt('Aucun parent valide pour %s dans %s', view($oElement), view($oElement->getDocument())), 'action/error');
            else {
            
              //("ancestor::*[namespace-uri() = '".SYLMA_NS_ACTIONBUILDER."' and @id-node][position() = 1]");
              
              $oParentNode = $oElement->getDocument()->get("//*[@id='{$oParent->getAttribute('id-node')}']");
              
              $sPath = '#'.$oParentNode->getId().' > '.$oRefNode->getCSSPath($oParentNode);
              $oElement->setAttribute('path-node', $sPath);
              
              // to avoid too much pass, save path
              
              $oPreviousParent = $oElement->getParent();
              $sPreviousPath = $sPath;
            }
            
          } else $oElement->setAttribute('path-node', $sPreviousPath);
        }
      }// else dspm(xt('Impossible de créer l\'évènement %s', view($oElement)), 'action/error');
    }
  }
  
  /**
   * Build cross references
   */
  
  private function buildReference($oElement) {
    
    $oRefNode = null;
    
    if ($sRefAxis = $oElement->getAttribute('extract-ref')) $oElement->removeAttribute('extract-ref');
    
    switch ($sRefAxis) {
      
      case 'parent' :
        
        if (!$oElement->isRoot()) $oRefNode = $oElement->getParent();
        else dspm(xt('ActionBuilder : Référence impossible, l\'objet %s n\'a pas de parent dans %s', view($oElement), $this->getAction()->getPath()->parse()), 'action/error');
        
      break;
      
      case 'child' :
      default : 
        
        if ($oElement->hasChildren() && $oElement->getFirst()->isElement()) {
          
          // first look in children
          
          foreach ($oElement->getChildren() as $oChild) {
            
            if ($oChild->getNamespace() != SYLMA_NS_ACTIONBUILDER) {
              
              $oRefNode = $oChild;
              break;
            }
          }
          
          // children are all action-builder go far
          
          if (!$oRefNode) $oRefNode = $oElement->get(".//*[namespace-uri() != '".SYLMA_NS_ACTIONBUILDER."']", $this->aNS); 
          
        } else dspm(xt('ActionBuilder : Référence impossible, l\'objet %s n\'a pas d\'enfant valide dans %s', view($oElement), $this->getAction()->getPath()->parse()), 'action/error');
        
      break;
    }
    
    return $oRefNode;
  }
  
  /**
   * Transform to xml js objects
   */
  
  public function buildResult($oScript) {
    
    // Parse as JSON xml => array() then add the result in Controler
    
    $oTemplate = new XSL_Document(SYLMA_PATH_ACTIONBUILDER.'/index.xsl', MODE_EXECUTION);
    // dspf($oScript);
    if ($oResult = $oTemplate->parseDocument($oScript)) {
      // dspf($oResult);
      //dspm(get_class(Controler::getWindow()));
      list(, $aResult) = $oResult->toArray();
      
      $sID = uniqid();
      
      Controler::addResult(json_encode($aResult), $sID);
      
      // Add methods in JS element
      
      if ($this->isFirstPass()) {
        
        // add tree build call
        
        // $sPath = addQuote(Controler::getPath()->getSimplePath().'.txt');
        $aKeys = array_keys($aResult);
        $sRootKey = addQuote($aKeys[0]);
        
        if (Controler::countResults()) Controler::getWindow()->addOnLoad("sylma.loadTree($sRootKey, '$sID')");
        // $oTemplate->setParameter('node-id', $sRoot);
        
        $oTemplate = new XSL_Document(SYLMA_PATH_ACTIONBUILDER.'/methods.xsl', MODE_EXECUTION);
        $sMethods = $oTemplate->parseDocument($this->oMethods, false);
        
        //dspm(new HTML_Tag('pre', $sMethods));
        if (Controler::getWindowSettings()->testAttribute('javascript', true)) Controler::getWindow()->addJS('', $sMethods);
        else Controler::addResult($sMethods, uniqid());
        
      }
      
    } else dspm('Action-Builder : Aucune balises récupérées !', 'action/warning');
  }
}

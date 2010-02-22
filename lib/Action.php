<?php

class XML_Action extends XML_Document {
  
  private $oPath = null;
  private $sName = '';
  private $aVariables = array();
  private $oRedirect = null;
  private $sStatut = 'void';
  private $aProcessors = array();
  private $aNS = array('le' => NS_EXECUTION, 'le' => NS_INTERFACE, 'xsl', NS_XSLT );
  
  // stats & infos resume
  private $aStats = array();
  public $aSubActions = array();
  private $oResume = null;
  
  public function __construct($mPath = null, $oRedirect = null, $aProcessors = array()) {
    
    if ($mPath) { // allow anonymouse action
      
      if ($mPath instanceof XML_Path) $this->oPath = $mPath;
      else $this->oPath = new XML_Path($mPath, false);
      
      if (!$oRedirect) $oRedirect = new Redirect;
      $this->setRedirect($oRedirect);
      
      if ($aProcessors) {
        
        foreach ($aProcessors as $oProcessor) $oProcessor->startAction($this);
        $this->aProcessors = $aProcessors;
      }
      
      parent::__construct((string) $this->getPath(), MODE_EXECUTION);
      
    } else parent::__construct();
  }
  
  private function getDirectory() {
    
    $sParent = '';
    
    if (!$this->getPath()->getFile()) dspm(array(t('Chemin introuvable pour l\'action'), $this->getPath()), 'action/error');
    else {
      
      $sParent = $this->getPath()->getFile()->getParent();
      $sParent = ($sParent == '/') ? $sParent : $sParent.'/';
    }
    
    return $sParent;
  }
  
  public function getPath() {
    
    return $this->oPath;
  }
  
  private function getStatut() {
    
    return $this->sStatut;
  }
  
  private function setStatut($sStatut) {
    
    $this->sStatut = $sStatut;
  }
  
  private function getAbsolutePath($sPath) {
    
    return Controler::getAbsolutePath($sPath, $this->getDirectory());
  }
  
  private function loadInterface($oInterface) {
    
    $oResult = null;
    $sMethod = '';
    $aArguments = array();
    
    if ($oInterface = Action_Controler::setInterface($oInterface)) {
      
      $sClassName = $oInterface->read('ns:name');
      if ($sFile = $oInterface->read('ns:file')) $sFile = $this->getAbsolutePath($sFile);
      
      if ($oConstruct = $oInterface->get('ns:method-construct')) {
        
        if ($oConstruct->hasChildren()) {
          
          $aArguments = $this->parseArguments($oConstruct, array(), true);
          if (!$aArguments && ($oConstruct->query('ns:argument[@required="false"]')->length != $oConstruct->query('ns:argument')->length)) {
            
            dspm('Erreur dans les arguments, impossible de construire l\'objet', 'action/warning');
            return null;
          }
        }
      }
      
      $oObject = $this->buildClass($sClassName, $sFile, $aArguments);
      
      if (($sMethod = $this->getPath()->getIndex()) && is_string($sMethod)) {
        
        $oElement = new XML_Element('li:'.$sMethod, null, array('get-redirect' => 'true'), NS_INTERFACE);
        list($oSubResult, $bSubResult) = $this->runInterfaceMethod($oObject, $oElement, $this);
        
        if ($bSubResult) $oResult = $oSubResult;
        else $oResult = $oObject;
        
      } else $oResult = $oObject;
    }
    
    return $oResult;
  }
  
  private function getVariable($sKey) {
    
    if (array_key_exists($sKey, $this->aVariables)) return $this->aVariables[$sKey];
    else {
      
      dspm(xt('La variable "%s" n\'existe pas dans %s !', new HTML_Strong($sKey), $this->getPath()->parse()), 'action/error');
      return null;
    }
  }
  
  private function setVariable($sKey, $mValue) {
    
    if ($mValue) $this->aVariables[$sKey] = $mValue;
    else if (array_key_exists($sKey, $this->aVariables)) unset($this->aVariables[$sKey]);
  }
  
  private function setVariableElement($oElement, $mVariable) {
    
    if ($sVariable = $oElement->getAttribute('set-variable')) {
      
      $this->setVariable($sVariable, $mVariable);
      if (Controler::useStatut('action/report')) dspm(xt('Ajout de la variable "%s" : %s', $sVariable, Controler::formatResource($mVariable)), 'action/report');
    }
  }
  
  public function runInterfaceList($mObject, $oElement, $bStatic = false) {
    
    $mResult = null;
    $aResults = array();
    
    if (is_array($mObject)) $mObject = new Action_Array($mObject);
    
    if (is_object($mObject) || $bStatic) $oInterface = Action_Controler::getInterface($mObject);
    else $oInterface = null;
    
    foreach ($oElement->getChildren() as $oChild) {
      
      if ($oChild->isElement()) {
        
        if ($oChild->getNamespace() == NS_INTERFACE) {
          
          list($mResult, $bReturn) = $this->runInterfaceMethod($mObject, $oChild, $oInterface, $bStatic);
          
          if ($bReturn) $aResults[] = $mResult;
          
        } else if ($oProcessor = $this->getProcessor($oChild->getNamespace())) {
          
          $mResult = $this->loadProcessor($oChild, $oProcessor);
          //$oChild->remove();
          
          if (Controler::useStatut('action/report')) dspm(array(
            t('Construction [ifc] :'),
            Controler::formatResource($mResult),
            $oChild->messageParse()), 'action/report');
          
        } else {
          
          dspm(array(xt('runInterfaceList() : L\'élément suivant n\'est pas permis dans %s ', $this->getPath()->parse()), $oChild->messageParse()), 'action/error');
          $oChild->remove();
        }
        
      } else $aResults[] = $oElement->getValue();
    }
    
    if ($aResults) {
      
      if (count($aResults) == 1) $mResult = $aResults[0];
      else $mResult = new XML_NodeList($aResults);
    }
    
    return array($mResult, ($aResults));
  }
  
  private function runInterfaceMethod($mObject, $oElement, $oInterface, $bStatic = false) {
    
    $oResult = null;
    $bReturn = false;
    $sActionMethod = $oElement->getName(true);
    
    if (!$oInterface) {
      
      $aArguments = array();
      
      if ($sActionMethod == 'if') {
        
        if ($mObject) foreach ($oElement->getChildren() as $oChild)
          $aArguments[] = $this->buildArgument($oChild);
        
      } else if ($sActionMethod == 'if-not') {
        
        if (!$mObject) foreach ($oElement->getChildren() as $oChild)
          $aArguments[] = $this->buildArgument($oChild);
        
      } else dspm(array(xt('Instruction inconnue dans %s sur %s', $this->getPath()->parse(), Controler::formatResource($mObject)), $oElement->messageParse()), 'action/error');
      
      if ($oElement->testAttribute('return') !== false) {
        
        foreach ($aArguments as $mArgument) if ($mArgument) $oResult = $mArgument;
        if ($oResult) $bReturn = true;
      }
      
    } else {
      
      if (!$oMethod = $oInterface->get("ns:method[@path='$sActionMethod']")) {
        
        dspm(array(xt('Méthode "%s" inexistante dans l\'interface "%s"', new HTML_Strong($oElement->getName(true)), new HTML_Strong($oInterface->read('ns:name'))), $oElement->messageParse()), 'action/warning');
        
      } else {
        
        // @return (bool) : erase & replace parent result up-to caller
        $bReturn = $oElement->testAttribute('return');
        if ($bReturn === null) $bReturn = $oMethod->testAttribute('return-default');
        if ($bReturn === null) $bReturn = false;
        
        // @le:format (string) : force children in one var with type indicated
        
        if ($sFormat = $oElement->getAttribute('format', NS_EXECUTION)) {
          
          $aArguments = array('index' => array($this->parseBaseType($sFormat, $oElement)));
          $oElement->cleanChildren();
          
        } else $aArguments = $this->loadElementArguments($oElement);
        
        // check name in interface
        
        if (!$sMethod = $oMethod->getAttribute('name')) {
          
          dspm('Interface invalide, attribut \'nom\' manquant', 'action/error');
          
        } else {
          
          // control arguments with the interface
          $aArgumentsPatch = $this->parseArguments($oMethod, $aArguments, $oElement->testAttribute('get-redirect'));
          
          // run method
          if ($aArgumentsPatch) $oResult = $this->runMethod($mObject, $sMethod, $aArgumentsPatch, $bStatic);
          else dspm(xt('Arguments invalides pour la méthode "%s" dans "%s"', new HTML_Strong($oElement->getName(true)), $this->getPath()->parse()), 'action/notice');
          
          // check variable
          $this->setVariableElement($oElement, $oResult);
          
          $bSubReturn = false;
          
          // run children
          if ($oElement->hasChildren()) list($oSubResult, $bSubReturn) = $this->runInterfaceList($oResult, $oElement);
          
          if ($bSubReturn) return array($oSubResult, true);
          else return array($oResult, $bReturn);
        }
      }
    }
    
    return array($oResult, $bReturn);
  }
  
  private function loadElementArguments($oElement) {
    
    // Load arguments and remove 'em from oElement
    
    $aArguments = array(
      'assoc' => array(),
      'index' => array());
    
    // $oTempElement = clone $oElement;
    
    foreach ($oElement->getChildren() as $iKey => $oChild) {
      
      if ($oChild->isElement()) {
        
        if (!$oChild->useNamespace(NS_INTERFACE)) {
          
          if (!$sName = $oChild->getAttribute('name', NS_EXECUTION)) {
            
            if ($oChild->getName(true) == 'argument' && $oChild->useNamespace(NS_EXECUTION)) $sName = $oChild->getAttribute('name');
            else $sName = '';
          }
          
          $mResult = $this->buildArgument($oChild->remove());
          
          if ($sName) $aArguments['assoc'][$sName] = $mResult;
          else $aArguments['index'][] = $mResult;
          
        }
        
      } else {
        
        $aArguments['index'][] = (string) $oChild;
        $oChild->remove();
      }
    }
    
    return $aArguments;
  }
  
  private function buildArgumentAction($oElement, $bParse) {
    
    $mResult = null;
    $bRun = false;
    
    // get the path
    
    if (!$sPath = $oElement->getAttribute('path')) {
      
      if (!$oElement->hasChildren()) {
        
        dspm(xt('Aucun chemin spécifié pour l\'action dans %s.', new HTML_Strong($this->getPath())), 'action/warning');
        
      } else {
        
        $sPath = (string) $this->buildArgument($oElement->getFirst());
        $oElement->getFirst()->remove();
      }
    }
    
    if ($sPath) {
      
      $oPath = new XML_Path($this->getAbsolutePath($sPath), $bParse);
      
      if ((string) $oPath == (string) $this->getPath()) {
        
        dspm(array(xt('Récursivité dans l\'action, chemin "%s" invalide !', $oPath->parse()), $oElement->messageParse()), 'action/error');
        
      } else {
        
        $oRedirect = clone $this->getRedirect();
        
        if ($oElement->hasChildren()) {
          
          $aArguments = $this->loadElementArguments($oElement);
          
          $oPath->pushIndex($aArguments['index']);
          $oPath->mergeAssoc($aArguments['assoc']);
        }
        
        $oAction = new XML_Action($oPath, $oRedirect, $this->aProcessors);
        $mResult = $oAction->parse();
        
        switch ($oAction->getStatut()) {
          
          case 'success' : break;
          case 'redirect' : 
            
            $this->setStatut('redirect');
            $this->setRedirect($mResult);
            $mResult = null;
            
          break;
          
          default : $mResult = null; break;
        }
        
        $bRun = true;
      }
    }
    
    return array($mResult, $bRun);
  }
  
  private function buildArgumentExecution($oElement) {
    
    $bRun = false;
    $mResult = null;
    
    $mSubResult = null;
    $bSubReturn = false;
    
    switch ($oElement->getName(true)) {
      
      case 'argument' :
        
        if ($oElement->hasChildren()) {
          
          $mResult = $this->buildArgument($oElement->getFirst());
          if ($oElement->countChildren() > 1) dspm(array(t('Un argument d\'action ne peut pas accepter plus d\'un argument enfant'), $oElement->messageParse()), 'action/warning');
          
        } else $mResult = null;
        
        if ($sFormat = $oElement->getAttribute('format', NS_EXECUTION)) $mResult = $this->parseBaseType($sFormat, $oElement);
        
      break;
      
      case 'test-argument' :
        
        $oArgument = new XML_Element('le:get-argument', null, array(
          'keep' => 'true'), NS_EXECUTION);
        
        if ($sName = $oElement->getAttribute('name')) $oArgument->setAttribute('name', $sName);
        
        if ($oElement->hasChildren()) {
          
          $mArgument = $this->buildArgument($oArgument);
          
          if ((($oElement->testAttribute('value') !== false) && $mArgument) ||
            (($oElement->testAttribute('value') === false) && !$mArgument)) {
            
            $mResult = $this->buildArgument($oElement->getFirst());
          }
        }
        
      break;
      
      case 'get-argument' :
        
        $bKeep = $oElement->testAttribute('keep');
        
        if ($sName = $oElement->getAttribute('name')) $mResult = $this->getPath()->getAssoc($sName, ($bKeep !== false));
        else if ($iIndex = $oElement->getAttribute('index')) $mResult = $this->getPath()->getIndex($iIndex, $bKeep);
        else $mResult = $this->getPath()->getIndex(0, $bKeep);
        
        $bRun = true;
        
      break;
      
      case 'get-settings' :
        
        $mResult = Controler::getSettings($oElement->read());
        
      break;
      
      case 'get-variable' :
        
        if (!$sVariable = $oElement->getAttribute('name')) {
          
          dspm(array(t('Aucune variable définie !'), $oElement->messageParse()), 'action/warning');
          
        } else {
          
          $mResult = $this->getVariable($sVariable);
          
          $bRun = true;
        }
        
      break;
      
      case 'interface' :
        
        if (!$sClassName = $oElement->getAttribute('class')) {
          
          dspm(array(
            xt('L\'élément %s doit spécifier une classe avec l\'attribut class', new HTML_Strong($oElement->getName())),
            new HTML_Tag('p', new HTML_Em($oElement->viewResume()))), 'action/error');
          
        } else {
          
          $oInterface = Action_Controler::getInterface($sClassName);
          $aArguments = array();
          
          if ($oConstruct = $oInterface->get('ns:method-construct')) {
            
            $aArguments = $this->parseArguments($oConstruct, $this->loadElementArguments($oElement));
          }
          
          if ($sPath = $oInterface->read('ns:file')) $sPath = $this->getAbsolutePath($sPath);
          
          $mResult = $this->buildClass($sClassName, $sPath, $aArguments);
          $bRun = true;
        }
        
      break;
      
      case 'direct-action' :
        
        $bParse = false;
      
      case 'document' :
        
        if ($oElement->countChildren() == 1) $mResult = new XML_Document($this->buildArgument($oElement->getFirst()));
        else {
          
          $mResult = new XML_Document('root');
          foreach ($oElement->getChildren() as $oChild) $mResult->add($this->buildArgument($oChild));
        }
        
      break;
      
      case 'action' :
        
        if (!isset($bParse)) $bParse = true;
        list($mResult, $bRun) = $this->buildArgumentAction($oElement, $bParse);
        
      break;
      
      case 'template' : $sClass = 'XSL_Document';
      case 'file' : 
        
        if (!isset($sClass)) $sClass = 'XML_Document';
        
        if (!($sPath = $oElement->getAttribute('path')) &&
          (!$oElement->hasChildren() || !($sPath = $this->buildArgument($oElement->getFirst()->remove())))) {
          
          dspm(array(
            xt('Aucun chemin spécifié pour le fichier dans %s.', new HTML_Strong($this->getPath())),
            new HTML_Tag('p', new HTML_Em($oElement->viewResume()))), 'action/warning');
          
        } else {
          
          $iMode = MODE_EXECUTION;
          
          if (($iTempMode = $oElement->getAttribute('mode')) && in_array($iTempMode, array(MODE_READ, MODE_WRITE, MODE_EXECUTION)))
            $iMode = $iTempMode; // TODO is it r'lly good
          
          $mResult = new $sClass($this->getAbsolutePath($sPath), $iMode);
          
          $bRun = true;
        }
        
      break;
      
      case 'recall' :
        
        if ($oElement->hasChildren()) {
          
          $mResult = $this->buildArgument($oElement->getChildren());
          $mResult = $this->buildArgument($mResult);
        }
        
      break;
      
      case 'php' :
      case 'special' : 
        
        $sSpecialName = $oElement->getAttribute('name');
        
      default :
        
        if (!isset($sSpecialName)) $sSpecialName = $oElement->getName(true);
        $aPhp = array('array', 'string', 'null', 'integer', 'int', 'boolean', 'bool');
        
        if (in_array($sSpecialName, $aPhp)) $mResult = $this->parseBaseType($sSpecialName, $oElement);
        else if ($aSpecial = Action_Controler::getSpecial($sSpecialName, $this, $this->getRedirect())) {
          
          if ($aSpecial['return']) $mResult = $aSpecial['variable'];
          list($mSubResult, $bSubReturn) = $this->runInterfaceList($aSpecial['variable'], $oElement, $aSpecial['static']);
          
        } else {
          
          dspm(array(t('Argument d\'action incorrect, nom inconnu'), $oElement->messageParse()), 'action/error');
        }
        
      break;
    }
    
    $this->setVariableElement($oElement, $mResult);
    
    if (Controler::useStatut('action/report')) dspm(array(
      t('Construction [exe] :'),
      Controler::formatResource($mResult),
      $oElement->messageParse()), 'action/report');
    
    // Run children if allowed
    
    if ($bRun && $oElement->hasChildren()) list($mSubResult, $bSubReturn) = $this->runInterfaceList($mResult, $oElement);
    
    // return attribute will define if main result is returned
    
    if ($oElement->testAttribute('return') === false) $mResult = null;
    $mResult = $bSubReturn ? $mSubResult : $mResult;
    
    if (Controler::useStatut('action/report') && $bSubReturn) dspm(array(
      t('Return sub-result :'),
      Controler::formatResource($mSubResult),
      $oElement->messageParse()), 'action/report');
    
    // Clone some attribute when element is an le:action
    
    if ($oElement->isElement() && $oElement->getName(true) == 'action' && $oElement->useNamespace(NS_EXECUTION) && is_object($mResult)) {
      
      if (($mResult instanceof XML_Document) || ($mResult instanceof XML_Element))
        $mResult->cloneAttribute($oElement, array('class', 'style', 'id'));
      else if ($mResult instanceof XML_NodeList && $mResult->length && $mResult->item(0)->isElement())
        $mResult->item(0)->cloneAttribute($oElement, array('class', 'style'));
      
    }
    
    return $mResult;
  }
  
  public function buildArgument($oElement) {
    
    $mResult = null;
    $sAction = 'Construction';
    
    if ($oElement instanceof XML_Element) { // XML_Element
      
      if ($oElement->useNamespace(NS_EXECUTION)) {
        
        /* Execution */
        
        $mResult = $this->buildArgumentExecution($oElement);
        
      } else if ($oElement->useNamespace(NS_INTERFACE)) {
        
        /* Interface */
        
        dspm(array(t('Aucune méthode ne peut être appellée ici !'), $oElement->messageParse()), 'action/error');
        $mResult = null;
        
      } else if ($oProcessor = $this->getProcessor($oElement->getNamespace())) {
        
        /* Other Processors */
        
        $sAction = 'Processus';
        $mResult = $this->loadProcessor($oElement, $oProcessor);
        
      } else {
        
        /* Unknown namespace -> copy element */
        
        $sAction = 'Copie';
        
        $mResult = clone $oElement;
        $mResult->cleanChildren();
        
        $mResult->add($this->buildArgument($oElement->getChildren()));
      }
      
    } else if ($oElement instanceof XML_NodeList) {
      
      $sAction = 'Liste';
      $oContainer = new XML_Element();
      foreach ($oElement as $oChild) $oContainer->add($this->buildArgument($oChild));
      
      $mResult = $oContainer->getChildren();
      
    } else if ($oElement->isText()) {
      
      $sAction = 'Texte';
      
      if ($oElement instanceof XML_Text) $mResult = (string) $oElement;
      else $mResult = $oElement->getValue();// CData
    }
    
    if (Controler::useStatut('action/report')) dspm(array(
      'Build ['.t($sAction).'] :',
      Controler::formatResource($mResult),
      $oElement->messageParse()), 'action/report');
    
    return $mResult;
  }
  
  private function loadProcessor($oElement, $oProcessor) {
    
    $mResult = $oProcessor->loadElement($oElement);
    
    /*if ($oElement->hasElementChildren()) {
      
      if ($oProcessor->useInterface()) list($mSubResult, $bSubReturn) = $this->runInterfaceList($mResult, $oElement);
      else $mSubResult = $this->buildArgument($oElement->getChildren());
      
      if ($mResult) $mResult->add($mSubResult);
      else $mResult = $mSubResult;
    }*/
    
    // $oProcessor->unloadElement();
    
    return $mResult;
  }
  
  private function getProcessor($sUri) {
    
    return array_key_exists($sUri, $this->aProcessors) ? $this->aProcessors[$sUri] : null;
  }
  
  private function parseBaseType($sName, $oElement) {
    
    $mResult = null;
    
    switch ($sName) {
      
      case 'array' :
        
        if ($oElement->getChildren()->length == 1 && $oElement->getFirst()->isText()) {
          
          // 1 child text
          
          if (!$sSeparator = $oElement->getAttribute('separator')) $sSeparator = ',';
          $mResult = explode($sSeparator, $oElement->read());
          
        } else {
          
          // 0..n child(ren) element
          
          $mResult = array();
          
          foreach ($oElement->getChildren() as $oChild) {
            
            $mArgument = $this->buildArgument($oChild);
            
            if ($sKey = $oChild->getAttribute('key')) $mResult[$sKey] = $mArgument;
            else $mResult[] = $mArgument;
          }
        }
        
      break;
      
      case 'bool' :
      case 'boolean' :
        
        $mResult = $this->buildArgument($oElement->getFirst());
        
        if (is_string($mResult)) $mResult = strtobool($mResult, true);
        else $mResult = (bool) $mResult;
        
      break;
      case 'int' : $mResult = intval($this->buildArgument($oElement->getFirst())); break;
      case 'integer' : $mResult = intval($this->buildArgument($oElement->getFirst())); break;
      
      case 'string' :
        
        $aArguments = array();
        
        if (!$sSeparator = $oElement->getAttribute('separator')) $sSeparator = '';
        
        foreach ($oElement->getChildren() as $oChild) $aArguments[] = $this->buildArgument($oChild);
        
        if (strtobool($oElement->getAttribute('quote'))) $aArguments = addQuote($aArguments);
        
        if (count($aArguments) > 1) $mResult = implode($sSeparator, $aArguments);
        else if ($aArguments) $mResult = (string) $aArguments[0];
        
      break;
      
      case 'null' : $mResult = null; break;
      
      default : dspm(array(xt('Type \'%s\' de base inconnu !', $sName), $oElement->messageParse()), 'action/error'); break;
    }
    
    return $mResult;
  }
  
  private function parseArguments($oMethod, $aSourceArguments, $bRedirect = false) {
    
    $bAssoc = false;
    
    if ($bRedirect) $aArguments = array_merge($this->getPath()->getArgument('index'), $this->getPath()->getArgument('assoc'));
    else $aArguments = array_merge(array_val('index', $aSourceArguments, array()), array_val('assoc', $aSourceArguments, array()));
    // if ($bRedirect) dsp($aArguments);
    // CALL argument
    
    $oChildren = $oMethod->getChildren();
    
    $aResultArguments = array();
    $bError = false;
    
    if ($oChildren->length == 1 && $oChildren->item(0)->getName() == 'multiple-arguments') {
      
      $oArguments = $oChildren->item(0);
      
      // Multiple arguments (undefined number)
      
      $iRequired = intval($oArguments->getAttribute('required-count'));
      
      if (!$iRequired || count($aArguments) >= $iRequired) {
        
        $aFormats = array();
        foreach($oArguments->getChildren() as $oFormat) $aFormats[] = $oFormat->read();
        
        foreach ($aArguments as $iArgument => $mArgument) {
          
          if ($mArgument !== null) {
            
            if ($this->validArgumentType($mArgument, $aFormats, $oMethod)) {
              
              $aResultArguments[] = $mArgument;
              
              // remove oRedirect argument
              if ($bRedirect) $this->getPath()->getIndex();
            }
          }
        }
        
      } else {
        
        dspm(xt('Pas assez d\'arguments dans %s!', new HTML_Strong($oMethod->getName(false))), 'action/warning');
        $bError = true;
      }
      
    } else {
      
      // Normal arguments (defined number)
      
      foreach($oChildren as $iArgument => $oChild) {
        
        $sName = $oChild->getAttribute('name');
        $bAssoc = $bExist = false;
        
        if ($sName && array_key_exists($sName, $aArguments)) {
          
          $mArgument = $aArguments[$sName];
          $bAssoc = $bExist = true;
          
        } else if (array_key_exists($iArgument, $aArguments)) {
          
          $mArgument = $aArguments[$iArgument];
          $bExist = true;
        }
        
        if ($bExist) {
          
          if ($bRedirect) {
            
            if ($bAssoc) $this->getPath()->getAssoc($sName);
            else $this->getPath()->getIndex();
          }
          
          $aFormats = array();
          
          if ($oChild->hasChildren()) foreach ($oChild->getChildren() as $oFormat) $aFormats[] = $oFormat->read();
          else if ($sFormat = $oChild->getAttribute('format')) $aFormats[] = $sFormat;
          
          $bError = !$this->validArgumentType($mArgument, $aFormats, $oMethod);
          
          if (!$bError) {
            
            $aResultArguments[] = $mArgument;
          }
          
        } else if ($oChild->testAttribute('required') !== false) {
          
          dspm(xt('L\'argument requis %s est absent dans %s',
            new HTML_Strong($oChild->getAttribute('name')),
            $this->getPath()->parse()), 'action/warning');
          
          $bError = true;
        }
      }
    }
    
    if (!$bError) {
      
      $aEvalArguments = array();
      
      foreach ($aResultArguments as $mIndex => $mArgument) $aEvalArguments[] = "\$aArguments['arguments']['$mIndex']";
      $sArguments = implode(', ', $aEvalArguments);
      
      return array(
        'string' => $sArguments,
        'arguments' => $aResultArguments,
      );
      
    }
    
    return false;
  }
  
  private function validArgumentType(&$mArgument, $aFormats, $oElement) {
    
    if (!$aFormats) return true;
    
    if (is_object($mArgument)) {
      
      $sActualFormat = get_class($mArgument);
      foreach ($aFormats as $sFormat) if ($mArgument instanceof $sFormat) return true;
      
    } else {
      
      if (is_numeric($mArgument)) {
        
        if (is_integer($mArgument) || ctype_digit($mArgument)) {
          
          $sActualFormat = 'php-integer';
          $mArgument = intval($mArgument);
          
        } else {
          
          $sActualFormat = 'php-float';
          $mArgument = floatval($mArgument);
        }
        
      } else $sActualFormat = 'php-'.strtolower(gettype($mArgument));
      
      if (in_array($sActualFormat, $aFormats)) return true;
    }
    
    dspm(array(
      xt('L\'argument [%s] n\'est pas du type : %s dans %s',
        // Controler::formatResource($mArgument),
        new HTML_em($sActualFormat),
        new HTML_Strong(implode(', ', $aFormats)),
        $this->getPath()->parse()),
      $oElement->messageParse()), 'action/warning');
    
    return false;
  }
  
  private function runMethod($mObject, $sMethodName, $aArguments = array(), $bStatic = false) {
    
    // Contrôle de l'existence de la méthode
    
    if (method_exists($mObject, $sMethodName) || method_exists($mObject, '__call')) {
      
      // Lancement de l'action
      $oResult = null;
      
      $sCaller = $bStatic ? '::' : '->';
      $sObject = $bStatic ? $mObject : '$mObject';
      $sArguments = $aArguments ? $aArguments['string'] : '';
      
      eval("\$oResult = $sObject$sCaller\$sMethodName($sArguments);");
      
      if (Controler::useStatut('action/report')) {
        
        $aDspArguments = array();
        foreach ($aArguments['arguments'] as $mArgument) $aDspArguments[] = Controler::formatResource($mArgument, false);
        
        $oArguments = new XML_NodeList($aDspArguments);
        
        if (!$bStatic) {
          
          eval("\$oObject = $sObject;");
          $mObject = Controler::formatResource($oObject);
          
        } else $mObject = $sObject;
        
        dspm(array(
        t('Evaluation : '),
        Controler::formatResource($oResult),
        " = ",
        $mObject,
        "$sCaller$sMethodName(",
        $oArguments->implode(', '),
        ");"), 'action/report');
      }
      
      return $oResult;
      
    } else dspm(xt('La méthode "%s" n\'existe pas dans la classe "%s" !', new HTML_Strong($sMethodName.'()'), get_class($mObject)), 'action/error');
    
    return null;
  }
  
  private function buildClass($sClassName, $sFile = '', $aArguments = array()) {
    
    if ($sFile) {
      
      // Include du fichier
      
      $sFile = MAIN_DIRECTORY.$sFile;
      
      if (file_exists($sFile)) require_once($sFile);
      else dspm(xt('Fichier "%s" introuvable !', new HTML_Strong($sFile)), 'action/warning');
    }
    
    // Contrôle de l'existence de la classe
    
    if (Controler::isAdmin()) $sError = xt('Action impossible (la classe "%s" n\'existe pas) !', new HTML_Strong($sClassName));
    else $sError = t('Page introuvable, veuillez corriger l\'adresse !');
    
    // if (!class_exists($sClassName)) Controler::errorRedirect($sError);
    if (class_exists($sClassName)) {
      
      $sAction = $aArguments ? $aArguments['string'] : '';
      
      // Création de la classe
      
      eval("\$oAction = new \$sClassName($sAction);");
      
      if (Controler::useStatut('action/report')) {
        
        if ($aArguments) {
          
          $aDspArguments = array();
          foreach ($aArguments['arguments'] as $mArgument) $aDspArguments[] = Controler::formatResource($mArgument);
          
          $oArguments = new XML_NodeList($aDspArguments);
          $sArguments = $oArguments->implode(', ');
          
        } else $sArguments = '';
        
        dspm(array(
        t('Evaluation : ')."\$oAction = new $sClassName(",
        $sArguments,
        ");"), 'action/report');
      }
      
      return $oAction;
      
    } else dspm($sError, 'action/warning');
  }
  
  public function setRedirect($oRedirect) {
    
    $this->oRedirect = $oRedirect;
  }
  
  public function getRedirect() {
    
    return $this->oRedirect;
  }
  
  private function validateArgument($oChild, $iArgument) {
    
    $bRequired = ($oChild->testAttribute('required') !== false);
    $bAssoc = false;
    $bResult = true;
    
    if ($mKey = $oChild->getAttribute('name')) {
      
      $bAssoc = true;
      $mArgument = $this->getPath()->getAssoc($mKey, true);
      
    } else {
      
      if (!$mKey = $oChild->getAttribute('index')) $mKey = $iArgument;
      $mArgument = $this->getPath()->getIndex($mKey, true);
    }
    
    if ($bRequired && $mArgument === null) {
      
      dspm(xt('L\'argument "%s" est manquant dans %s !', new HTML_Strong($mKey), $this->getPath()->parse()), 'error');dspf($this->getPath()->getIndex(1));
      $bResult = false;
      
    } else {
      
      $bReplace = false;
      
      // Argument is here
      
      if ($mArgument) {
        
        // Argument has value
        
        $aFormats = array();
        
        if ($sFormat = $oChild->getAttribute('format')) $aFormats[] = $sFormat; // TODO NS BUGS
        else if ((!$aFormats = $oChild->query('tr:format', array('tr' => NS_XHTML))->toArray()) &&
          ($oFormat = $oChild->get('le:formats', 'le', NS_EXECUTION))) {
          
          $aFormats = $oFormat->getChildren()->toArray();
        }
        
        if (!$this->validArgumentType($mArgument, $aFormats, $oChild)) {
          
          dspm(xt('L\'argument "%s" est au mauvais format dans %s !', new HTML_Strong($mKey), $this->getPath()->parse()), 'error');
          $bResult = false;
          
        } else {
          
          // Argument is good format
          
          /* Validation */
          
          if (($oValidate = $oChild->get('le:validate', 'le', NS_EXECUTION)) && $oValidate->hasChildren()) {
            
            if (!$mArgument = $this->buildArgument($oValidate->getFirst())) {
              
              if ($oValidate->testAttribute('required', true)) {
                
                dspm(xt('L\'argument "%s" est invalide dans %s !', new HTML_Strong($mKey), $this->getPath()->parse()), 'action/error');
              }
              
              $bResult = false;
              
            } else {
              
              if ($oValidate->testAttribute('return')) $bReplace = true;
              
              if (Controler::useStatut('action/report')) {
                
                $sArgumentType = $bAssoc ? 'assoc' : 'index';
                dspm(xt('Argument : %s [%s]', Controler::formatResource($mArgument), new HTML_Em($sArgumentType)), 'action/report');
              }
            }
          }
        }
      }
      
      /* Default value */
      
      if (($mArgument === null || !$bResult) && ($oDefault = $oChild->get('le:default', 'le', NS_EXECUTION)) && $oDefault->hasChildren()) {
        
        // Argument has no value and is required
        
        if ((!$mResult = $this->buildArgument($oDefault->getFirst())) && $oDefault->testAttribute('required')) {
          
          dspm(xt('Argument "%s" valeur par défaut invalide dans %s !', new HTML_Strong($mKey), $this->getPath()->parse()), 'action/error');
          $bResult = false;
          
        } else $bReplace = true;
      }
      
      /* Hypothetical replacement */
      
      if ($bReplace) {
        
        $bResult = true;
        
        if ($bAssoc) $this->getPath()->setAssoc($mKey, $mResult);
        else $this->getPath()->setIndex($mKey, $mResult);
        
        if (Controler::useStatut('action/report')) {
          
          $sArgumentType = $bAssoc ? 'assoc' : 'index';
          dspm(xt('Argument redéfini : %s &gt; %s', Controler::formatResource($mResult), new HTML_Em($sArgumentType)), 'action/report');
        }
      }
    }
    
    return $bResult;
  }
  
  public function loadSettings($oSettings) {
    
    $bResult = true;
    $iArgument = 0;
    
    if ($oSettings && $oSettings->hasChildren()) {
      
      foreach ($oSettings->getChildren() as $oChild) {
        
        switch ($oChild->getName(true)) {
          
          case 'name' : break;
          case 'argument' :
            
            $bResult = $this->validateArgument($oChild, $iArgument);
            $iArgument++;
            
          break;
          case 'processor' :
            
            if ($sNamespace = $oChild->getAttribute('namespace')) {
              
              if ($sSource = $oChild->getAttribute('source')) {
                
                $oSource = new XML_Document($this->getAbsolutePath($sSource));
                $sPath = $oSource->read('//le:settings/le:processor/@path', $this->aNS);
                
              } else $sPath = $oChild->getAttribute('path');
              
              if ($sPath) {
                
                if (!array_key_exists($sNamespace, $this->aProcessors)) {
                  
                  $oAction = new XML_Element(
                    'le:action',
                    new XML_Element('le:self', null, array('return' => 'true'), NS_EXECUTION),
                    array('path' => $sPath),
                    NS_EXECUTION);
                  
                  if (!$oResult = $this->buildArgument($oAction)) dspm(xt('Processeur %s introuvable dans %s', $sNamespace, $this->getPath()->parse()), 'action/error');
                  else {
                    
                    $oResult->startAction($this);
                    $this->aProcessors[$sNamespace] = $oResult;
                  }
                }
                
              } else dspm(xt('Processor [%s]: chemin introuvable ! %s', new HTML_Strong(t('namespace')), $oChild->messageParse()), 'action/error');
              
            } else dspm(xt('Processor : attribut %s manquant %s', new HTML_Strong(t('namespace')), $oChild->messageParse()), 'action/error');
            
          break;
        }
      }
      
      $oSettings->remove();
    }
    
    return $bResult;
  }
  
  public function getResume() {
    
    if (!$this->oResume) $this->oResume = new XML_Element('action', null, array('path' => $this->getPath()));
    
    return $this->oResume;
  }
  
  public function resumeFile($oFile, $bFirstTime) {
    
    if (!$oFiles = $this->getResume()->get('files')) $oFiles = $this->getResume()->addNode('files');
    
    $oResume = $oFile->parseXML();
    if ($bFirstTime) $oResume->setAttribute('first-time' , 1);
    
    $oFiles->add($oResume);
  }
  
  public function resumeAction($oAction) {
    
    $this->aSubActions[] = $oAction->viewResume();
  }
  
  public function viewResume() {
    
    $oAction = $this->getResume();
    $oStats = $oAction->addNode('stats');
    $aStats = array();
    
    // build stats
    foreach ($this->aStats as $sName => $fValue) {
      
      $fValue = float_format($fValue);
      
      $aStats[$sName] = $oStats->addNode('stat', null, array(
        'name' => $sName,
        'value' => $fValue,
        'sub-value' => $fValue));
      
      $aStats[$sName]->addAttributes(array());
    }
    
    // build sub-actions
    if ($this->aSubActions) {
      
      // add children
      $oSubActions = $oAction->addNode('sub-actions');
      
      foreach ($this->aSubActions as $oSubAction) $oSubActions->add($oSubAction);
      
      // evaluate stat weight with children's
      foreach ($this->aStats as $sName => $fValue) {
        
        $oSubStats = $oSubActions->query("action/stats/stat[@name='$sName']");
        
        $fResultValue = $fValue;
        
        if ($fValue) $fWeight = 1;
        else $fWeight = 0;
        
        foreach ($oSubStats as $oStat) {
          
          $fSubValue = $oStat->getAttribute('sub-value');
          
          if ($fValue) $fSubWeight = (1 / $fValue) * $fSubValue;
          else $fSubWeight = 0;
          
          $oStat->addAttributes(array(
            'weight-color' => inter_color($fSubWeight),
            'total-value' => float_format($fValue, 2)));
          
          $fWeight -= $fSubWeight;
          $fResultValue -= $fSubValue;
        }
        
        $aStats[$sName]->addAttributes(array(
          'sub-weight-color' => inter_color($fWeight),
          'value' => float_format($fResultValue)));
      }
    }
    
    return $oAction;
  }
  
  public function parse($aStats = array()) {
    
    $oResult = null;
    $bStats = false;
    
    // Load stats
    
    if (SYLMA_ACTION_STATS && Controler::getUser()->isMember('0')) {
      
      $bStats = true;
      
      if (!$aStats) {
        
        $aStats = XML_Controler::getStats();
        $aStats['time'] = microtime(true);
      }
      
      Controler::infosOpenAction($this);
    }
    
    // begin check & parsing
    
    if ($this && !$this->isEmpty()) {
      
      $oRoot = $this->getRoot();
      $oDocument = new XML_Document($oRoot);
      
      if (Controler::useStatut('action/report')) {
        
        $oSeek = new HTML_Span(t('Début'), array('style' => 'color: green;'));
        dspm(array(xt('%s de l\'exécution du fichier %s', $oSeek, $this->getPath()->parse()), new HTML_Hr), 'action/report');
      }
      
      
      switch ($oRoot->getNamespace()) {
        
        /* Execution */
        
        case NS_EXECUTION : 
          
          switch ($oRoot->getName(true)) {
            
            // action
            
            case 'action' :
              
              if ($this->loadSettings($oDocument->get('le:settings', 'le', NS_EXECUTION))) {
                
                $oResult = new XML_Document('temp');
                
                $oMethod = new XML_Element('li:add', $oDocument->getRoot()->getChildren(), null, NS_INTERFACE);
                $this->runInterfaceMethod($oResult, $oMethod, Action_Controler::getInterface($oResult, $this->getRedirect()));
                
                if (!$oResult->isEmpty()) $oResult = $oResult->getRoot()->getChildren();
                
              } else dspm(xt('L\'action %s n\'a pas été exécuté', $this->getPath()), 'action/error');
              
            break;
            
            case 'interface' :
              
              if (!$oSettings = $this->get('le:settings', 'le', NS_EXECUTION)) {
                
                dspm(xt('Action %s invalide, aucuns paramètres !', new HTML_Strong($this->getPath())), 'action/warning');
                
              } else {
                
                $sClass = $oSettings->read('le:class', 'le', NS_EXECUTION);
                $oSettings->remove();
                
                if ($oRoot->hasChildren()) {
                  
                  $aArguments = $this->loadElementArguments($oRoot);
                  $this->getPath()->pushIndex($aArguments['index']);
                  $this->getPath()->mergeAssoc($aArguments['assoc']);
                }
                
                if ($oInterface = Action_Controler::getInterface($sClass)) {
                  
                  $oResult = $this->loadInterface($oInterface);
                  list($oSubResult, $bSubReturn) = $this->runInterfaceList($oResult, $oRoot);
                }
              }
              
            break;
            
            default :
              
              dspm(xt('L\'élément racine %s n\'est pas un élément racine valide du fichier d\'action %s ', new HTML_Strong($oRoot->getName(false)), new HTML_Strong($this->getPath())), 'action/warning');
              
            break;
          }
          
        break;
        
        /* Interface */
        
        case NS_INTERFACE :
          
          $oResult = $this->loadInterface($oRoot);
          
        break;
        
        default :
          
          dspm(xt('Ceci n\'est pas un interface valide %s', new HTML_Strong($oRoot->getName(false))), 'action/warning');
          
        break;
        
      }
      
      if (Controler::useStatut('action/report')) {
        
        $oSeek = new HTML_Span(t('Fin'), array('style' => 'color: red;'));
        dspm(array(xt('%s de l\'exécution du fichier %s', $oSeek,$this->getPath()->parse()), new HTML_Hr), 'action/report');
      }
      
      if ($this->getStatut() == 'void') $this->setStatut('success');
      if (is_object($oResult) && $oResult instanceof Redirect) {
        
        $this->setStatut('redirect');
        $this->setRedirect($oResult);
      }
    }
    
    /* Processors */
    
    if ($this->aProcessors) foreach ($this->aProcessors as $oProcessor) $oProcessor->stopAction();
    
    // save stats
    
    if ($bStats) {
      
      $this->aStats['time'] = microtime(true) - $aStats['time'];
      
      foreach (XML_Controler::getStats() as $sKey => $iValue) {
        
        if (!array_key_exists($sKey, $aStats)) $aStats[$sKey] = 0;
        $this->aStats[$sKey] = $iValue - $aStats[$sKey];
      }
      
      Controler::infosCloseAction($this);
    }
    
    /* Final */
    
    switch ($this->getStatut()) {
      
      case 'redirect' :
        
        return $this->getRedirect();
        
      break;
      
      case 'success' : // Success
        
        return $oResult;
        
      break;
      
      case 'error' : // Error
        
        dspm(xt('Action "%s" impossible, argument(s) invalide(s) !', new HTML_Strong($this->getPath())), 'error');
        
      break;
      
      case 'void' : // Pas de document (404)
      default :
        
        if ($this->getPath()) dspm(xt('Action "%s" impossible, document inexistant ou invalide !', $this->getPath()->parse()), 'action/warning');
        
      break;
    }
    
    return null;
  }
}

class HTML_Action extends HTML_Tag {
  
  public function __construct() {
    
    parent::__construct('div');
    $this->addClass('action');
  }
}

class Action_Array {
  
  private $aArray = array();
  public $length;
  protected $iIndex = 0;
  
  public function __construct($aArray) {
    
    $this->aArray = $aArray;
    $this->length = count($aArray);
  }
  
  public function item($mKey) {
    
    if (array_key_exists($mKey, $this->aArray)) return $this->aArray[$mKey];
    else return null;
  }
  
  public function rewind() {
    
    $this->iIndex = 0;
  }
  
  public function next() {
    
    $this->iIndex++;
  }
  
  public function key() {
    
    return $this->iIndex;
  }
  
  public function current() {
    
    return $this->aArray[$this->iIndex];
  }
  
  public function valid() {
    
    return ($this->iIndex < count($this->aArray));
  }
}

class XML_Path {
  
  private $aArguments = array('index' => array(), 'assoc' => array());
  
  private $sExtension = '';
  private $sOriginalPath = '';
  private $oFile = null;
  
  public function __construct($sPath, $bParse = true, $aArguments = array()) {
    
    // Remove arguments following '?' of type ..?arg1=val&arg2=val..
    
    if ($iAssoc = strpos($sPath, '?')) {
      
      $sAssoc = substr($sPath, $iAssoc + 1);
      $sPath = substr($sPath, 0, $iAssoc);
      
      $aAssoc = explode('&', $sAssoc);
      
      foreach ($aAssoc as $sArgument) {
        
        $aArgument = explode('=', $sArgument);
        
        if (count($aArgument) == 1) $aArguments[] = $aArgument[0]; // only name
        else $aArguments[$aArgument[0]] = $aArgument[1]; // name and value
      }
    }
    
    foreach ($aArguments as $sKey => $sArgument) {
      
      $aArgument = explode('=', $sArgument);
      
      if (!$sArgument) {
        
        $sArgument = $sKey;
        $sKey = 0;
      }
      
      if (is_integer($sKey)) $this->aArguments['index'][] = $sArgument;
      else $this->aArguments['assoc'][$sKey] = $sArgument;
    }
    
    $this->sOriginalPath = $sPath;
    $this->setPath($sPath);
    if ($bParse) $this->parsePath();
    
    // echo $sPath;
    // dsp($this->aArguments);
  }
  
  public function parsePath() {
    
    global $aActionExtensions;
    
    $sResultPath = '';
    $bError = false;
    $bUseIndex = true;
    
    $oDirectory = Controler::getDirectory();
    $oFile = null;
    
    if ($this->getPath() == '/') $aPath = array();
    else {
      
      $aPath = explode('/', $this->getPath());
      array_shift($aPath);
    }
    
    do {
      
      $sSubPath = $aPath ? $aPath[0] : '.';
      
      if (!$oSubDirectory = $oDirectory->getDirectory($sSubPath)) {
        
        foreach ($aActionExtensions as $sExtension) if ($oFile = $oDirectory->getFile($sSubPath.$sExtension, false)) break;
        
      } else $oDirectory = $oSubDirectory;
      
      if (!$oFile && (!$aPath || !$oSubDirectory)) {
        
        if ($oFile = $oDirectory->getFile('index.eml')) $bUseIndex = true;
        else if ($oDirectory->checkRights(MODE_EXECUTION)) {
          
          $bError = true;
          dspm(xt('Le listing de répertoire n\'est pas encore possible :| : "%s"', new HTML_Strong($oDirectory)), 'action/warning');
          
        } else {
          
          $bError = true;
          dspm(xt('Le répertoire "%s" ne peut pas être listé, droits insuffisants', new HTML_Strong($oDirectory)), 'action/warning');
        }
        
      } else array_shift($aPath);
      
    } while (!$oFile && !$bError);
    
    if (!$bError) {
      
      if ($bUseIndex) $this->sOriginalPath = (string) $oFile->getParent();
      else $this->sOriginalPath = (string) $oFile;
      
      // if ($sExtension = $this->getExtension()) $this->sOriginalPath .= '.'.$sExtension;
      
      $this->setFile($oFile);
      $this->pushIndex($aPath);
      $this->setPath($oFile);
      
    } else $this->setPath('');
  }
  
  public function parseExtension($bRemove) {
    
    $sPath = $this->getPath();
    
    preg_match('/\.(\w+)$/', $sPath, $aResult, PREG_OFFSET_CAPTURE);
    
    if (count($aResult) == 2 && ($sExtension = $aResult[1][0])) {
      
      $iExtension = $aResult[1][1];
      if ($bRemove) $this->setPath(substr($sPath, 0, $iExtension - 1).substr($sPath, $iExtension + strlen($sExtension)));
      
      $this->sExtension = $sExtension;
    }
    
    return $this->getExtension();
  }
  
  public function getDirectory() {
    
    if ($this->getFile()) return $this->getFile()->getParent();
    else return null;
  }
  
  public function getFile() {
    
    return $this->oFile;
  }
  
  public function setFile($oFile) {
    
    $this->oFile = $oFile;
  }
  
  public function setPath($sPath) {
    
    $this->sPath = (string) $sPath;
  }
  
  public function getOriginalPath() {
    
    return $this->sOriginalPath;
  }
  
  public function isValid() {
    
    return (bool) $this->getPath();
  }
  
  public function getPath() {
    
    return $this->sPath;
  }
  
  public function getExtension() {
    
    return $this->sExtension;
  }
  
  public function setArgument($sArgument, $aArgument = array()) {
    
    if (is_array($aArgument)) $this->aArguments[$sArgument] = $aArgument;
    else dspm(xt('Liste d\'argument invalide, ce n\'est pas un tableau'), 'action/error');
  }
  
  public function getArgument($sArgument = null) {
    
    if (!$sArgument) return $this->aArguments;
    else {
      
      if (!array_key_exists($sArgument, $this->aArguments)) $this->aArguments[$sArgument] = array();
      return $this->aArguments[$sArgument];
    }
  }
  
  public function shiftIndex($mArguments) {
    
    if (is_array($mArguments)) $this->aArguments['index'] = array_merge($mArguments, $this->aArguments['index']);
    else array_unshift($mArguments, $this->aArguments['index']);
  }
  
  public function pushIndex($mArguments) {
    
    if (is_array($mArguments)) $this->aArguments['index'] = array_merge($this->aArguments['index'], $mArguments);
    else array_push($this->aArguments['index'], $mArguments);
  }
  
  private function setKey($sArray, $sKey, $mValue) {
    
    if ($mValue) $this->aArguments[$sArray][$sKey] = $mValue;
    else if (array_key_exists($sKey, $this->aArguments[$sArray])) unset($this->aArguments[$sArray][$sKey]);
  }
  
  public function setIndex($iKey, $mValue = '') {
    
    $this->setKey('index', $iKey, $mValue);
    if ($mValue) $this->aArguments['index'] = array_values($this->aArguments['index']);
  }
  
  public function setAssoc($sKey, $mValue = '') {
    
    $this->setKey('assoc', $sKey, $mValue);
  }
  
  public function mergeAssoc($aArguments) {
    
    $this->aArguments['assoc'] = array_merge($this->aArguments['assoc'], $aArguments);
  }
  
  public function getAllIndex($bRemove = true) {
    
    $aIndex = $this->aArguments['index'];
    if ($bRemove) $this->aArguments['index'] = array();
    
    return implode('/', $aIndex);
  }
  
  public function getIndex($iKey = 0, $bKeep = false) {
    
    $mResult = $this->getKey('index', $iKey, $bKeep);
    if ($mResult !== null) $this->aArguments['index'] = array_merge($this->aArguments['index']);
    
    return $mResult;
  }
  
  public function getAssoc($sKey, $bKeep = false) {
    
    return $this->getKey('assoc', $sKey, $bKeep);
  }
  
  private function getKey($sArray, $mKey, $bKeep) {
    
    if (array_key_exists($mKey, $this->aArguments[$sArray])) {
      
      $mResult = $this->aArguments[$sArray][$mKey];
      if (!$bKeep) unset($this->aArguments[$sArray][$mKey]);
      
      return $mResult;
    }
    
    return null;
  }
  
  public function parse() {
    
    $sPath = (string) $this;
    return new HTML_A(PATH_EDITOR.'?path='.$sPath, $sPath);
  }
  
  public function __toString() {
    
    return $this->getPath();
  }
}

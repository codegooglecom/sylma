<?php

class XML_Action extends XML_Document {
  
  const MONITOR_NS = 'http://www.sylma.org/action/monitor';
  
  private $oParent = null; // parent action, null if not an action
  private $oPath = null;
  private $oSettings = null;
  private $oPathResume = null;
  private $sName = '';
  private $aVariables = array();
  private $oRedirect = null;
  private $sStatut = null;
  private $aProcessors = array();
  private $aNS = array(
    'le' => SYLMA_NS_EXECUTION,
    'li' => SYLMA_NS_INTERFACE,
    'xsl' => SYLMA_NS_XSLT,
    'lem' => self::MONITOR_NS);
  
  private $aQueries = array();
  
  // stats & infos resume
  private $aStats = array();
  public $aSubActions = array();
  private $oResume = null;
  
  public function __construct($mPath = null, $oRedirect = null, array $aProcessors = array(), $oParent = null) {
    
    $this->oParent = $oParent;
    
    if ($mPath) { // allow anonymouse action
      
      if ($mPath instanceof XML_Path) {
        
        if ($mPath->getPath()) $this->oPath = $mPath;
        else {
          
          $this->dspm(xt('Chemin invalide'), 'action/error');
          $this->oPath = new XML_Path(Sylma::get('action/error/page'));
        }
        
      } else $this->oPath = new XML_Path($mPath, array(), true);
      
      if (!$oRedirect) $oRedirect = new Redirect;
      $this->setRedirect($oRedirect);
      
      $this->aProcessors = $aProcessors;
      
      parent::__construct((string) $this->getPath(), MODE_EXECUTION);
      
    } else parent::__construct();
  }
  
  public function getParent() {
    
    return $this->oParent;
  }
  
  private function getDirectory() {
    
    $sParent = '';
    
    if (!$this->getPath()->getFile()) $this->log(txt('Cannot find @file %s', $this->getPath()), 'error');
    else $sParent = $this->getPath()->getFile()->getParent();
    
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
    $bStatic = false;
    $aArguments = array();
    
    if ($oInterface = ActionControler::setInterface($oInterface)) {
      
      $sClassName = $oInterface->readByName('name');
      if ($sFile = $oInterface->readByName('file')) $sFile = $this->getAbsolutePath($sFile);
      
      if ($oConstruct = $oInterface->getByName('method-construct')) {
        
        if ($oConstruct->hasChildren()) {
          
          $aArguments = $this->parseArguments($oConstruct, array(), true);
          
          if (!$aArguments && ($oConstruct->query('ns:argument[not(@required="false")]'))) {
            
            $this->log(txt('Bad arguments, cannot build the object @class %s', $sClassName), 'warning');
            return null;
          }
        }
      }
      
      $mObject = $this->buildClass($sClassName, $sFile, $aArguments);
      
      if (($sMethod = $this->getPath()->getIndex()) && is_string($sMethod)) {
        
        // simulate action interface call, with args recup (get-redirect) and default return (return)
        
        $oElement = new XML_Element('li:'.$sMethod, null, array('get-redirect' => 'true', 'return' => 'true'), SYLMA_NS_INTERFACE);
        list($oSubResult, $bSubResult) = $this->runInterfaceMethod($mObject, $oElement, $this, $bStatic);
        
        if ($bSubResult) $oResult = $oSubResult;
        else $oResult = $mObject;
        
      } else $oResult = $mObject;
    }
    
    return $oResult;
  }
  
  private function getVariable($sKey, $bDebug = true) {
    
    if (array_key_exists($sKey, $this->aVariables)) return $this->aVariables[$sKey];
    else if ($bDebug) {
      
      $this->log(txt('The variable %s does not exist !', $sKey), 'action/error');
    }
    
    return null;
  }
  
  public function setVariables(array $aVariables) {
    
    $this->aVariables = array_merge($this->aVariables, $aVariables);
  }
  
  public function setVariable($sKey, $mValue) {
    
    $this->aVariables[$sKey] = $mValue;
    /*if ($mValue) 
    else if (array_key_exists($sKey, $this->aVariables)) unset($this->aVariables[$sKey]);*/
  }
  
  private function setVariableElement($oElement, $mVariable) {
    
    if (($sVariable = $oElement->getAttribute('set-variable', SYLMA_NS_EXECUTION)) ||
      ($sVariable = $oElement->getAttribute('set-variable'))) { // TODO remove no namespaced one
      
      $this->setVariable($sVariable, $mVariable);
      if (Controler::useStatut('action/report')) dspm(xt('Ajout de la variable "%s" : %s', $sVariable, Controler::formatResource($mVariable)), 'action/report');
    }
  }
  
  public function runInterfaceList($mObject, $oElement, $bStatic = false) {
    
    $mResult = null;
    $aResults = array();
    
    if (is_array($mObject)) $mObject = new Action_Array($mObject);
    
    if (is_object($mObject) || $bStatic) $oInterface = ActionControler::getInterface($mObject);
    else $oInterface = null;
    
    foreach ($oElement->getChildren() as $oChild) {
      
      if ($oChild->isElement()) {
        
        if ($oChild->getNamespace() == SYLMA_NS_INTERFACE) {
          
          list($mResult, $bReturn) = $this->runInterfaceMethod($mObject, $oChild, $oInterface, $bStatic);
          
          if ($bReturn) $aResults[] = $mResult;
          
        } else if ($oProcessor = $this->getProcessor($oChild->getNamespace())) {
          
          $mResult = $this->runProcessor($oChild, $oProcessor);
          //$oChild->remove();
          
          if (Controler::useStatut('action/report')) dspm(array(
            t('Construction [ifc] :'),
            Controler::formatResource($mResult),
            $oChild->messageParse()), 'action/report');
          
        } else {
          
          $this->log(txt('@element %s is not allowed in %s', $oChild->getName(false), $oElement->getName(false)), 'error');
          $oChild->remove();
        }
        
      } else $aResults[] = $oElement->getValue();
    }
    
    if ($aResults) {
      
      if (count($aResults) == 1) $mResult = $aResults[0];
      else $mResult = $aResults;//$mResult = new XML_NodeList($aResults);
    }
    
    return array($mResult, ($aResults));
  }
  
  /**
   * Beginning of all XML_Action, run an object's method
   * @param mixed $mObject The object having the method
   * @param XML_Element $oElement The element containing the method parameters
   * @param XML_Document $oInterface The interface document of the object
   * @param boolean $bStatic Type of call to the object, if true it's a static::call
   * @return array [0] The return value, [1] A boolean that indicates if the value have to be keeped
   */
  private function runInterfaceMethod($mObject, $oElement, DOMNode $oInterface = null, $bStatic = false) {
    
    $oResult = null;
    $bReturn = false;
    $sActionMethod = $oElement->getName(true);
    $sPathInterface = $oInterface ? ($oInterface->getDocument() ? $oInterface->getDocument()->getPath() : '[none]') : '[none]';
    
    if ($sActionMethod == 'if') {
      
      if (Controler::useStatut('action/report'))
        dspm(xt('Condition [%s] : %s &gt; %s', view($oElement), view($mObject), view((bool) $mObject)), 'action/report');
      
      if ($mObject) $oResult = $this->buildArgument($oElement->getChildren());
      $bReturn = $oElement->testAttribute('return', true);
      
    } else if ($sActionMethod == 'if-not') {
      
      if (Controler::useStatut('action/report'))
        dspm(xt('Condition [%s] : %s &gt; %s', view($oElement), view($mObject), view((bool) $mObject)), 'action/report');
      
      if (!$mObject) $oResult = $this->buildArgument($oElement->getChildren());
      $bReturn = $oElement->testAttribute('return', true);
      
    } else if (!$oInterface) {
      
      $this->log(txt('No available interface for @element %s', $oElement->getPath()), 'warning');
      
    } else if (!$oMethod = $oInterface->get("ns:method[@path='$sActionMethod']")) {
      
      $this->log(txt('Unknown method in @element %s for interface @file %s', $oElement->getPath(), $sPathInterface), 'warning');
      
    } else {
      
      // @return (bool) : erase & replace parent result up-to caller
      $bReturn = $oElement->testAttribute('return');
      if ($bReturn === null) $bReturn = $oMethod->testAttribute('return-default', false);
      
      // @le:format (string) : force children in one var with type indicated
      
      if ($sFormat = $oElement->getAttribute('format', SYLMA_NS_EXECUTION)) {
        
        $aArguments = array('index' => array($this->parseBaseType($sFormat, $oElement)));
        $oElement->cleanChildren();
        
      } else $aArguments = $this->loadElementArguments($oElement);
      
      // send all arguments in addition with previously defined
      if ($oElement->testAttribute('send-all-arguments')) {
        
        if ($aArguments) $aArguments = array_merge_recursive($aArguments, $this->getPath()->getAllArguments());
        else $aArguments = $this->getPath()->getAllArguments();
      }
      
      // check name in interface
      
      if (!$sMethod = $oMethod->getAttribute('name')) {
        
        $this->log(txt('Invalid interface %s, name attribute missing in method %s', $sPathInterface, $sActionMethod),'error');
        
      } else {
        
        // control arguments with the interface
        $aArgumentsPatch = $this->parseArguments($oMethod, $aArguments, $oElement->testAttribute('get-redirect'));
        
        // run method
        if ($aArgumentsPatch) $oResult = $this->runMethod($mObject, $sMethod, $aArgumentsPatch, $bStatic);
        
        // format output with @le:function
        if ($oElement->hasAttribute('function', SYLMA_NS_EXECUTION)) {
          
          $oResult = $this->runFunction($oElement, $oResult);
        }
        
        // check variable
        $this->setVariableElement($oElement, $oResult);
        
        $bSubReturn = false;
        
        // run children
        if ($oElement->hasChildren()) list($oSubResult, $bSubReturn) = $this->runInterfaceList($oResult, $oElement);
        
        if ($bSubReturn) return array($oSubResult, true);
        else return array($oResult, $bReturn);
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
        
        if (!$oChild->useNamespace(SYLMA_NS_INTERFACE)) {
          
          // get @le:name or le:argument/@name
          
          if (!$sName = $oChild->getAttribute('name', SYLMA_NS_EXECUTION)) {
            
            if ($oChild->getName() == 'argument' && $oChild->useNamespace(SYLMA_NS_EXECUTION)) $sName = $oChild->getAttribute('name');
            else $sName = '';
          }
          
          $mResult = $this->buildArgument($oChild);
          $oChild->remove();
          
          if ($sName) $aArguments['assoc'][$sName] = $mResult;
          else $aArguments['index'][] = $mResult;
        }
        
      } else {
        
        $aArguments['index'][] = $this->buildArgument($oChild);
        $oChild->remove();
      }
    }
    
    //echo 'fin : '.$oElement->getName();
    return $aArguments;
  }
  
  private function buildArgumentAction($oElement) {
    
    $mResult = null;
    $bRun = false;
    
    // get the path
    
    if (!$sPath = $oElement->getAttribute('path')) {
      
      if (!$oElement->hasChildren()) {
        
        $this->log(txt('No path defined for action'), 'warning');
        
      } else {
        
        $sPath = (string) $this->buildArgument($oElement->getFirst());
        $oElement->getFirst()->remove();
      }
    }
    
    if ($sPath) {
      
      $oPath = new XML_Path($this->getAbsolutePath($sPath), array(), true);
      
      if ((string) $oPath == (string) $this->getPath()) {
        
        $this->dspm(txt('Recursive call with @file %s !', $sPath), 'error');
        
      } else {
        
        $oRedirect = clone $this->getRedirect();
        
        // get arguments
        
        if ($oElement->hasChildren()) $aArguments = $this->loadElementArguments($oElement);
        else $aArguments = array();
        
        if ($oElement->testAttribute('send-all-arguments')) {
          
          if ($aArguments) $aArguments = array_merge_recursive($aArguments, $this->getPath()->getAllArguments());
          else $aArguments = $this->getPath()->getAllArguments();
        }
        
        if ($aArguments) {
          
          $oPath->pushIndex($aArguments['index']);
          $oPath->mergeAssoc($aArguments['assoc']);
        }
        
        // build
        
        $oAction = new XML_Action($oPath, $oRedirect, $this->aProcessors, $this);
        $mResult = $oAction->parse();
        
        // check result
        
        switch ($oAction->getStatut()) {
          
          case 'success' : break;
          case 'redirect' : 
            
            $this->setStatut('redirect');
            $this->setRedirect($mResult);
            
            $mResult = null;
            
            if (Controler::useStatut('action/report')) {
              
              dspm(xt('Redirection vers %s', $mResult->getPath()), 'action/report');
            }
            
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
    $bStatic = false;
    $mResult = null;
    
    $mSubResult = null;
    $bSubReturn = false;
    
    switch ($oElement->getName(true)) {
      
      case 'argument' :
        
        if ($oElement->hasChildren()) {
          
          if ($sFormat = $oElement->getAttribute('format', SYLMA_NS_EXECUTION)) $mResult = $this->parseBaseType($sFormat, $oElement);
          else if ($oElement->countChildren() <= 1) $mResult = $this->buildArgument($oElement->getFirst());
          else $this->log(txt('Invalid @element %s. The number of children is wrong', $oElement->getPath()), 'warning');
          
        } else $mResult = null;
        
      break;
      
      case 'test-argument' :
        
        $oArgument = new XML_Element('le:get-argument', null, array(
          'keep' => 'true'), SYLMA_NS_EXECUTION);
        
        if ($sName = $oElement->getAttribute('name')) $oArgument->setAttribute('name', $sName);
        
        if ($oElement->hasChildren()) {
          
          $mArgument = $this->buildArgument($oArgument);
          
          if ((($oElement->testAttribute('value') !== false) && $mArgument) ||
            (($oElement->testAttribute('value') === false) && !$mArgument)) {
            
            $mResult = $this->buildArgument($oElement->getFirst());
          }
        }
        
      break;
      
      case 'get-all-arguments' :
        
        $mResult = $this->getPath()->getAllArguments(true);
        $bRun = true;
        
      break;
      
      case 'get-argument' :
        
        $bKeep = $oElement->testAttribute('keep');
        
        if ($sName = $oElement->getAttribute('name')) {
          
          if ($this->getPath()->hasAssoc($sName)) $mResult = $this->getPath()->getAssoc($sName, ($bKeep !== false));
          else if ($oElement->testAttribute('required', false)) {
            
            $this->log(txt('Cannot find argument %s for @element %s', $sName, $oElement->getPath()), 'error');
          }
          
        } else if ($iIndex = $oElement->getAttribute('index')) $mResult = $this->getPath()->getIndex($iIndex, $bKeep);
        else $mResult = $this->getPath()->getIndex(0, $bKeep);
        
        $bRun = true;
        
      break;
      
      case 'get-settings' :
        
        $mResult = Controler::getSettings($oElement->read());
        
      break;
      
      case 'set-variable' :
        
        if (!$sName = $oElement->getAttribute('name')) {
          
          $this->log(txt('@attribute %s is missing in @element %s', 'name', $oElement->getPath()), 'error');
        }
        else {
          
          $mResult = $this->buildArgument($oElement->getChildren());
          $this->setVariable($sName, $mResult);
        }
        
      break;
      
      case 'get-variable' :
        
        if ((!$sVariable = $oElement->getAttribute('name'))) {
          
          $this->log(txt('Cannot load @element %s, no @attribute name given', $oElement->getPath()), 'warning');
          
        } else {
          
          $mResult = $this->getVariable($sVariable, $oElement->testAttribute('debug', true));
          
          $bRun = true;
        }
        
      break;
      
      case 'switch' :
        
        if ($oElement->getChildren()->length < 2) {
          
          $this->log(txt('Not enough argument for @element %s'), 'error');
          
        } else {
          
          if ($oElement->getFirst()->getName() == 'case') {
            
            $this->log(txt('@element case vorbidden as first argument in switch must set the value to check first'), 'error');
            
          } else {
            
            $mResult = array();
            $mTest = $this->buildArgument($oElement->getFirst()->remove());
            $bPrevious = false;
            
            foreach ($oElement->getChildren() as $oChild) {
              
              if (!$oChild->useNamespace(SYLMA_NS_EXECUTION) ||
                !($oChild->getName() == 'case' || $oChild->getName() == 'default')) {
                
                $this->log(txt('Vorbidden @element %s in @element %s', $oChild->getPath(), $oElement->getPath()), 'action/error');
                
              } else {
                
                if ($oChild->getName() == 'default') {
                  
                  // default
                  
                  if ($oChild != $oElement->getLast()) {
                    
                    $this->log(txt('@element %s should the last child of @element %s',
                      $oChild->getPath(), $oElement->getPath()), 'error');
                  }
                  else {
                    
                    $mResult[] = $this->buildArgument($oChild->getChildren());
                  }
                  
                } else {
                  
                  // case
                  
                  // compare values
                  if (!$mValue = $oChild->getAttribute('test')) {
                    
                    if (!$oChild->getChildren()->length) {
                      
                      $this->log(txt('@attribute %s is missing in @element %s', 'test', $oChild->getPath()), 'warning');
                    }
                    else {
                      
                      $mValue = $this->buildArgument($oChild->getFirst()->remove());
                    }
                  }
                  
                  // if same add value
                  if ($bPrevious || $mValue === $mTest) {
                    
                    if ($oChild->getChildren()->length) $mResult[] = $this->buildArgument($oChild->getChildren());
                    
                    if ($oChild->testAttribute('break', true)) break;
                    else $bPrevious = true;
                  }
                }
              }
            }
            
            if (count($mResult) == 1) $mResult = $mResult[0];
          }
        }
        
      break;
      
      case 'function' :
        
        $mResult = $this->runFunction($oElement);
        
      break;
      
      case 'interface' :
        
        if (!$sClassName = $oElement->getAttribute('class')) {
          
          $this->log(txt('@attribute %s is missing in @element %s', 'class', $oElement->getPath()), 'error');
          
        } else {
          
          $oInterface = ActionControler::getInterface($sClassName);
          
          $aArguments = array();
          
          if ($oConstruct = $oInterface->getByName('method-construct')) {
            
            $aArguments = $this->parseArguments($oConstruct, $this->loadElementArguments($oElement));
          }
          
          if ($sPath = $oInterface->readByName('file')) $sPath = $this->getAbsolutePath($sPath);
          
          $mResult = $this->buildClass($sClassName, $sPath, $aArguments);
          $bRun = true;
        }
        
      break;
      
      case 'document' :
        
        if ($oElement->hasChildren()) {
          
          $sClass = $oElement->getAttribute('class');
          
          switch ($sClass) {
            
            case 'xsl' :
              
              $mResult = new XSL_Document;
              $this->setGlobalVariables($mResult); // auto variables
              
            break;
            
            case 'xml' : 
            default : $mResult = new XML_Document; break;
          }
          
          $mResult->set($this->buildArgument($oElement->getFirst()));
          $oElement->getFirst()->remove();
          
          $bRun = true;
          
        }/* else {
          
          $mResult = new XML_Document('root');
          foreach ($oElement->getChildren() as $oChild) $mResult->add($this->buildArgument($oChild));
        }*/
        
      break;
      
      case 'element' : // used to handle html element
        
        if (!$oElement->hasChildren() || !$oElement->getFirst()->isElement()) {
          
          $this->log(txt('@element %s has no valid child', $oElement->getPath()), 'error');
          
        } else { // validated
          
          $mResult = $this->buildArgument($oElement->getFirst()->remove());
          $bRun = true;
        }
        
      break;
      
      /**
       * Create attribute XML_Attribute
       * [optional] attribute name : name of the builded attribute
       * first child : builded attribute name or-
       * second child : content of the attribute transformed to string
       */
      
      case 'attribute' :
        
        if (!$oElement->hasChildren() || !$oElement->getFirst()->isElement()) {
          
          $this->log(txt('@element %s has no valide child', $oElement->getPath()), 'error');
          
        } else { // validated
          
          if (!$sName = $oElement->getAttribute('name')) {
            
            if ($oElement->countChildren() < 2) {
              
              $this->log(txt('@attribute %s or at least one child is required in @element %s',
                'name', $oElement->getPath()), 'error');
              
            } else {
              
              $sName = (string) $this->buildArgument($oElement->getFirst()->remove());
            }
          }
          
          $sContent = (string) $this->buildArgument($oElement->getFirst()->remove());
          
          $mResult = new XML_Attribute($sName, $sContent);
        }
        
      break;
      
      case 'action' :
        
        list($mResult, $bRun) = $this->buildArgumentAction($oElement);
        
      break;
      
      case 'xquery' :
        
        if (!$oElement->hasChildren()) {
          
          $this->log(txt('@element %s must have some children', $oElement->getPath()), 'error');
          
        } else {
          
          if ($oElement->countChildren() > 1) { // first are namespaces
            
            $aNamespaces = $this->buildArgument($oElement->getFirst());
            $oElement->getFirst()->remove();
            
            if (!is_array($aNamespaces)) {
              
              $this->log(txt('The second argument in @element %s must return an array of namespaces',
                $oElement->getPath()), 'error');
            }
            
          } else $aNamespaces = array();
          
          // second or first come query
          
          if (!$oArgument = $this->buildArgument($oElement->getFirst())) {
            
            $this->log(txt('A child must return the query in @element %s', $oElement->getPath()), 'error');
            
          } else $mResult = new XML_XQuery($oArgument, $aNamespaces);
          
          // $bRun = true;
        }
        
      break;
      
      case 'template' : $sClass = 'XSL_Document';
      case 'file' : 
        
        if (!isset($sClass)) $sClass = 'XML_Document';
        
        $this->replaceAttributesVariables($oElement);
        // TOCHECK remove is dangerous >
        if (!($mPath = $oElement->getAttribute('path')) &&
          (!$oElement->hasChildren() || !($mPath = $this->buildArgument($oElement->getFirst()->remove())))) { 
          
          $this->log(txt('@attribute %s or a child is missing in @element %s.', 'path', $oElement->getPath()), 'warning');
          
        } else { // file found
          
          if ($oElement->getAttribute('output') == 'text') {
            
            if ($oFile = Controler::getFile((string) $mPath, $this->getDirectory(), true)) {
              
              $mResult = $oFile->read();
              //dspf($mResult);
              if ($oElement->testAttribute('parse-variables', false, SYLMA_NS_EXECUTION))
                $mResult = $this->replaceVariables($mResult, true);
            }
            
          } else {
            
            $iMode = MODE_EXECUTION;
            
            if (($iTempMode = $oElement->getAttribute('mode')) && in_array($iTempMode, array(MODE_READ, MODE_WRITE, MODE_EXECUTION)))
              $iMode = $iTempMode; // TODO is it r'lly good
            
            if (is_string($mPath)) $mPath = $this->getAbsolutePath($mPath);
            
            $mResult = new $sClass($mPath, $iMode, $oElement->testAttribute('include'));
            
            if ($sClass == 'XSL_Document') $this->setGlobalVariables($mResult); // auto variables
            
            $bRun = true;
          }
        }
        
      break;
      
      case 'recall' :
        
        if ($oElement->hasChildren()) {
          
          $mResult = $this->buildArgument($oElement->getChildren());
          $mResult = $this->buildArgument($mResult);
        }
        
      break;
      
      case 'namespace' :
        
        if ($sPrefix = $oElement->getAttribute('prefix')) {
          
          $mResult = $oElement->getNamespace($sPrefix);
        }
        else {
          
          $aNamespaces = array(
            'action' => SYLMA_NS_EXECUTION,
            'directory' => SYLMA_NS_DIRECTORY,
            'security' => SYLMA_NS_SECURITY,
            'interface' => SYLMA_NS_INTERFACE,
            'message' => SYLMA_NS_MESSAGES,
            'xsd' => SYLMA_NS_XSD);
          
          if (!$sNamespace = $oElement->read()) {
            
            $this->log(txt('Cannot find namespace with @element %s', $oElement->getPath()), 'error');
            
          } else if (!array_key_exists($sNamespace, $aNamespaces)) {
            
            $this->log(txt('No namespace prefixed with %s in @element %s', $sNamespace, $oElement->getPath()), 'error');
            
          } else $mResult = $aNamespaces[$sNamespace];
        }
        
      break;
      
      case 'ns' :
        
        $mResult = array();
        
        foreach (explode(',', $this->buildArgument($oElement->getChildren())) as $sPrefix) {
          
          $sPrefix = trim($sPrefix);
          
          if ($sPrefix{0} == '*') {
            
            if ($sNamespace = $oElement->getNamespace(null)) $mResult[substr($sPrefix, 1)] = $sNamespace;
            else $this->log(xt('No default namespace found with %s in @element %s', $sPrefix, $oElement->getPath()), 'warning');
            
          } else {
            
            if ($sNamespace = $oElement->getNamespace($sPrefix)) $mResult[$sPrefix] = $sNamespace;
            else $this->log(txt('No namespace found with %s in @element %s', $sPrefix, $oElement->getPath()), 'warning');
          }
        }
        
      break;
      case 'php' :
      case 'special' : 
        
        $sSpecialName = $oElement->getAttribute('name');
        
      default :
        
        if (!isset($sSpecialName)) $sSpecialName = $oElement->getName(true);
        $aPhp = array('array', 'string', 'null', 'integer', 'int', 'boolean', 'bool');
        
        if (in_array($sSpecialName, $aPhp)) $mResult = $this->parseBaseType($sSpecialName, $oElement);
        else if ($aSpecial = ActionControler::getSpecial($sSpecialName, $this, $this->getRedirect())) {
          
          $mResult = $aSpecial['variable'];
          if (!$oElement->hasAttribute('return')) $oElement->setAttribute('return', booltostr($aSpecial['return']));
          
          $bRun = true;
          $bStatic = $aSpecial['static'];
          
        } else {
          
          $this->log(txt('Unknown @element %s', $oElement->getPath()), 'error');
        }
        
      break;
    }
    
    $this->setVariableElement($oElement, $mResult);
    
    // if (Controler::useStatut('action/report')) dspm(array(xt('Exécution [%s] :', view($oElement)),view($mResult, false)), 'action/report');
    
    // Run children if allowed
    
    if ($bRun && $oElement->hasChildren()) list($mSubResult, $bSubReturn) = $this->runInterfaceList($mResult, $oElement, $bStatic);
    
    // return attribute will define if main result is returned
    
    if (!$oElement->testAttribute('return', true)) $mResult = null;
    $mResult = $bSubReturn ? $mSubResult : $mResult;
    
    return $mResult;
  }
  
  /**
   * Will set as parameters in a template, a selection of global variables that will be available
   * through all the XSL templates created in an XML_Action.
   * @param XSL_Template The template to set variables to
   */
  protected function setGlobalVariables(XSL_Document $oTemplate) {
    
    $oTemplate->setParameters(array(
      'sylma-user' => Controler::getUser()->getName(),
      'sylma-directory' => (string) $this->getDirectory(),
      'sylma-lang' => (string) Controler::getSettings('infos/lang'),
    ));
  }
  
  /**
   * Run the function indicate either in the @name of a le:function or in a @le:function of a li:*
   * The element's children will be use as argument, some functions can use more than one argument
   * @todo Must be normalized then documented
   * @param XML_Element $oElement The element to process
   * @return mixed The returned result of the function called
   */
  
  protected function runFunction(XML_Element $oElement, $mValue = null) {
    
    $mResult = null;
    
    if ($oElement->useNamespace(SYLMA_NS_EXECUTION)) {
      
      if (!$sName = $oElement->getAttribute('name')) {
        
        $this->log(txt('@attribute %s is missing in @element %s', 'name', $oElement->getPath()), 'error');
      }
      else {
        
        $mValue = $this->buildArgument($oElement->getChildren());
      }
      
    } else {
      
      if (!$sName = $oElement->getAttribute('function', SYLMA_NS_EXECUTION)) {
        
        $this->log(txt('@attribute %s is missing in @element %s', 'function', $oElement->getPath()), 'error');
      }
    }
    
    if ($sName) {
      
      switch ($sName) {
        
        case 'urlencode' : $mResult = urlencode($mValue); break;
        case 'add-quote' : $mResult = addQuote($mValue); break;
        case 'escape-path' : $mResult = '"'.xmlize($mValue).'"'; break;
        case 'format-date' :
          
          $mDate = $mValue;
          $sFormat = '';
          
          if ($mValue instanceof XML_NodeList) {
            
            $mDate = $mValue->item(0);
            $sFormat = $mValue->item(1);
          }
          
          if (!$mDate) {
            
            $this->log(txt('Invalid date %s in @element %s', $mDate, $oElement->getPath()), 'error');
          }
          else {
            
            if (!$sFormat) $sFormat = 'd.m.Y';
            $mResult = date($sFormat, strtotime($mDate));
          }
          
        break;
        
        default:
          
          $this->log(txt('Unknown function %s in @element %s', $sName, $oElement->getPath()), 'error');
      }
    }
    
    return $mResult;
  }
  
  /*
   * This is where append main transformation of XML nodes into objects/values that will be send to action process.
   * @param mixed $oArgument The XML node to process
   * @return mixed The result value to insert into result tree
   **/
  public function buildArgument($oArgument) {
    
    $mResult = null;
    $sAction = 'default';
    
    // msg
    
    if ($oArgument instanceof XML_Document) $oArgument = $oArgument->getRoot(); // XML_Document => XML_Element
    
    if ($oArgument instanceof XML_Element) { // XML_Element
      
      if ($oArgument->useNamespace(SYLMA_NS_EXECUTION)) {
        
        /* Execution */
        
        if (Controler::useStatut('action/report') && $oArgument->isComplex())
          dspm(xt('Build:%s [%s]',
            new HTML_Span('start', array('style' => 'color: green')),
            view($oArgument)), 'action/report');
        
        $mResult = $this->buildArgumentExecution($oArgument);
        
        // msg
        if (Controler::useStatut('action/report')) {
          
          if ($oArgument->isComplex()) {
            $oContent = xt('Build:%s [%s] &gt; ', new HTML_Span('end', array('style' => 'color: red')), view($oArgument));
          }
          else {
            $oContent = xt('Build [%s] &gt; ', view($oArgument));
          }
          
          dspm(array($oContent, view($mResult, false)), 'action/report');
        }
      }
      else if ($oArgument->useNamespace(SYLMA_NS_INTERFACE)) {
        
        /* Interface */
        
        $this->log(txt('Methods cannot be called in @element %s', $oArgument->getPath()), 'error');
        $mResult = null;
      }
      else if ($oProcessor = $this->getProcessor($oArgument->getNamespace())) {
        
        /* Other Processors */
        
        $this->replaceAttributesVariables($oArgument);
        $mResult = $this->runProcessor($oArgument, $oProcessor);
      }
      else {
        
        /* Unknown namespace -> copy element */
        if (Controler::useStatut('action/report')) dspm(xt('Copy [%s]', view($oArgument)), 'action/report');
        
        $mResult = $oArgument->cloneNode(false);
        
        $this->setVariableElement($oArgument, $mResult);
        
        if ($oArgument->hasChildren()) {
          
          // Avoid unuseful XML_Nodelist call
          if ($oArgument->countChildren() > 1) $oChildren = $oArgument->getChildren();
          else $oChildren = $oArgument->getFirst();
          
          $mResult->add($this->buildArgument($oChildren));
        }
        
        $this->replaceAttributesVariables($mResult);
      }
    }
    else if ($oArgument instanceof XML_NodeList) {
      
      /* NodeList */
      
      $aResult = array();
      foreach ($oArgument as $oChild) $aResult[] = $this->buildArgument($oChild);
      
      if ($aResult) {
        
        if (count($aResult) > 1) $mResult = $aResult;
        else $mResult = $aResult[0];
      }
      
      /*$oResult = new XML_Element('undefined');
      foreach ($oArgument as $oChild) $oResult->add($this->buildArgument($oChild));
      
      if ($oResult->hasChildren()) {
        
        if ($oResult->countChildren() == 1) $mResult = $oResult->getFirst();
        else $mResult = $oResult->getChildren();
      }*/
      
      if (Controler::useStatut('action/report'))
        dspm(array(xt('List [%s] &gt; ', view($oArgument)), view($mResult)), 'action/report');
    }
    else if ($oArgument instanceof XML_Text) {
      
      /* Text */
      
      $mResult = $oArgument->getValue();
      
      if ($oArgument->getParent()->testAttribute('parse-variables', false, SYLMA_NS_EXECUTION))
        $mResult = $this->replaceVariables($mResult, true);
      
      if (Controler::useStatut('action/report'))
        dspm(array(xt('Text [%s] &gt; ', view($oArgument)), view($mResult)), 'action/report');
    }
    else if ($oArgument instanceof XML_CData) {
      
      $mResult = clone $oArgument;
      
      if ($oArgument->getParent()->testAttribute('parse-variables', false, SYLMA_NS_EXECUTION))
        $mResult = $this->replaceVariables($mResult, true);
      
      if (Controler::useStatut('action/report'))
        dspm(array(xt('CData [%s] &gt; ', view($oArgument)), view($mResult)), 'action/report');
    }
    else if ($oArgument instanceof XML_Comment) {
      
      $mResult = clone $oArgument; // TODO : generate DOMComment
    }
    else if ($oArgument === null) {
      
      // TODO, something or not
    }
    else {
      
      // TODO, be more explicit
      $this->log(txt('Uknown object type %s', gettype($oArgument)), 'error');
    }
    
    return $mResult;
  }
  
  private function replaceAttributesVariables($oElement) {
    
    // Check attributes variables calls. Format : [$myvar]
    
    foreach ($oElement->getAttributes() as $oAttribute) {
      
      if ($sValue = $this->replaceVariables($oAttribute->getValue())) $oAttribute->set($sValue);
    }
  }
  
  private function replaceVariables($sTest, $bReturn = false) {
    
    // $sValue = unxmlize($sTest);
    $sValue = $sTest;
    preg_match_all('/\[\$([\w-]+)\]/', $sValue, $aResults, PREG_OFFSET_CAPTURE);
    
    if ($aResults && $aResults[0]) {
      
      $iSeek = 0;
      
      foreach ($aResults[1] as $aResult) {
        
        $iVarLength = strlen($aResult[0]) + 3;
        $sVarValue = (string) $this->getVariable($aResult[0]);
        
        $sValue = substr($sValue, 0, $aResult[1] + $iSeek - 2) . $sVarValue . substr($sValue, $aResult[1] + $iSeek - 2 + $iVarLength);
        
        $iSeek += strlen($sVarValue) - $iVarLength;
      }
      
      // return xmlize($sValue);
      return $sValue;
    }
    
    if ($bReturn) return $sTest;
    else return null;
  }
  
  private function runProcessor($oElement, $oProcessor) {
    
    $mResult = $oProcessor->loadElement($oElement, $this);
    
    
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
          $bNull = $oElement->testAttribute('use-null', true);
          
          foreach ($oElement->getChildren() as $oChild) {
            
            if (!$oChild->useNamespace(SYLMA_NS_INTERFACE)) {
              
              $mArgument = $this->buildArgument($oChild);
              
              if ($bNull || $mArgument !== null) {
                
                if ($sKey = $oChild->getAttribute('key', SYLMA_NS_EXECUTION)) $mResult[$sKey] = $mArgument;
                else $mResult[] = $mArgument;
              }
              
              $oChild->remove();
            }
          }
          
          list($mSubResult, $bSubReturn) = $this->runInterfaceList($mResult, $oElement);
          $this->setVariableElement($oElement, $mResult);
          
          if ($bSubReturn) $mResult = $mSubResult;
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
      
      default :
        
        $this->log(txt('Base type %s unknown in @element %s', $sName, $oElement->getPath()), 'error');
        
      break;
    }
    
    return $mResult;
  }
  
  private function parseArguments($oMethod, $aSourceArguments, $bRedirect = false) {
    
    $bAssoc = false;
    
    if ($bRedirect) $aArguments = array_merge($this->getPath()->getArgument('index'), $this->getPath()->getArgument('assoc'));
    else $aArguments = array_merge(array_val('index', $aSourceArguments, array()), array_val('assoc', $aSourceArguments, array()));
	
	// merge $_POST values
	if ($oMethod->testAttribute('use-post')) $aArguments = array_merge($aArguments, $_POST);
    
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
        
        $this->log(txt('Not enough arguments in @element %s!', $oMethod->getPath()), 'action/warning');
        $bError = true;
      }
      
    } else {
      
      // Normal arguments (defined number)
      
      $aMethodArguments = array();
      $bUseAssoc = false;
      
      foreach($oChildren as $iArgument => $oChild) {
        
        $sName = $oChild->getAttribute('name');
        $bAssoc = $bExist = false;
        
        if ($sName && array_key_exists($sName, $aArguments)) {
          
          $mArgument = $aArguments[$sName];
          $bUseAssoc = $bAssoc = $bExist = true;
          
        } else if (!$bUseAssoc && array_key_exists($iArgument, $aArguments)) {
          
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
          
          $bSubError = !$this->validArgumentType($mArgument, $aFormats, $oMethod, $oChild->testAttribute('allow-null', false));
          
          if (!$bSubError) $aResultArguments[] = $mArgument;
          if (!$bError) $bError = $bSubError;
          
        } else if ($oChild->testAttribute('required') !== false) {
          
          $this->log(txt('Required argument %s in @element %s is missing',
            $oChild->getAttribute('name'), $oMethod->getPath()), 'error');
          
          $bError = true;
          
        } else if ($bUseAssoc && !$oChild->isLast()) {
          
          $aResultArguments[] = null;
        }
      }
    }
    
    if (!$bError) {
      
      $aEvalArguments = array();
      
      // TODO array in array no more necessary due to use of Reflection
      
      foreach ($aResultArguments as $mIndex => $mArgument) $aEvalArguments[] = "\$aArguments['arguments']['$mIndex']";
      $sArguments = implode(', ', $aEvalArguments);
      
      return array(
        'string' => $sArguments,
        'arguments' => $aResultArguments,
      );
    }
    
    return false;
  }
  
  private function runMethod($mObject, $sMethodName, $aArguments = array(), $bStatic = false) {
    
    // Contrôle de l'existence de la méthode
    
    if (is_string($mObject) && !class_exists($mObject)) {
      
      $this->log(txt('Class %s does not exist', $mObject));
    }
    else if (method_exists($mObject, $sMethodName) || method_exists($mObject, '__call')) {
      
      // Lancement de l'action
      $oResult = null;
      
      $sCaller = $bStatic ? '::' : '->';
      $sObject = $bStatic ? $mObject : '$mObject';
      $sArguments = $aArguments ? $aArguments['string'] : '';
      
      // TODO
      eval("\$oResult = $sObject$sCaller\$sMethodName($sArguments);");
      
      if (Controler::useStatut('action/report')) {
        
        $aDspArguments = array();
        foreach ($aArguments['arguments'] as $mArgument) $aDspArguments[] = view($mArgument, false);
        
        $oArguments = new XML_NodeList($aDspArguments);
        
        if (!$bStatic) {
          
          eval("\$oObject = $sObject;");
          $mObject = view($oObject);
          
        } else $mObject = $sObject;
        
        dspm(array(
        t('Evaluation : '),
        view($oResult),
        " = ",
        $mObject,
        "$sCaller$sMethodName(",
        $oArguments->implode(', '),
        ");"), 'action/report');
      }
      
      return $oResult;
      
    }
    else {
      
      $this->log(txt('Method %s does not exist in @class %s', $sMethodName,
        (is_string($mObject) ? $mObject : get_class($mObject))), 'error');
    }
    
    return null;
  }
  
  private function buildClass($sClassName, $sFile = '', $aArguments = array()) {
    
    if ($aArguments) $aArguments = $aArguments['arguments'];
    
    if (Controler::loadClass($sClassName, $sFile) && ($oObject = Controler::buildClass($sClassName, $aArguments))) {
      
      if (Controler::useStatut('action/report')) {
        
        if ($aArguments) {
          
          $aDspArguments = array();
          foreach ($aArguments as $mArgument) $aDspArguments[] = view($mArgument);
          
          $oArguments = new XML_NodeList($aDspArguments);
          $sArguments = $oArguments->implode(', ');
          
        } else $sArguments = '';
        
        $sCaller = 
        
        dspm(array(
        t('Instanciation : ')."\$oAction = new $sClassName(",
        $sArguments,
        ");"), 'action/report');
      }
      
      return $oObject;
    }
  }
  
  public function setRedirect($oRedirect) {
    
    $this->oRedirect = $oRedirect;
  }
  
  public function getRedirect() {
    
    return $this->oRedirect;
  }
  
  private function validateArgument($oChild, $iArgument, $bPost = false) {
    
    $bRequired = ($oChild->testAttribute('required') !== false);
    $bAssoc = false;
    $bResult = true;
    $mArgument = null;
    
    // first load argument from name or index
    
    if ($mKey = $oChild->getAttribute('name')) {
      
      $bAssoc = true;
      
      if ($oChild->testAttribute('use-post', $bPost)) {
        
        if ($oPost = $this->getRedirect()->getDocument('post')) {
          
          if ($mArgument = $oPost->getByName($mKey)) $mArgument = $mArgument->read();
          $this->getPath()->setAssoc($mKey, $mArgument);
        }
        
      } else $mArgument = $this->getPath()->getAssoc($mKey, true);
      
    } else {
      
      if ($oChild->testAttribute('use-post')) {
        
        $this->log('@attribute %s is missing in @element %s', 'name', $oChild->getPath());
      }
      else {
        
        if (!$mKey = $oChild->getAttribute('index')) $mKey = $iArgument;
        $mArgument = $this->getPath()->getIndex($mKey, true);
      }
    }
    
    // then check if required
    
    if ($bRequired && $mArgument === null) { // TODO : check if && !$mArgument is required
      
      $this->log(txt('Argument %s is missing', $mKey), 'error');
      $bResult = false;
      
    } else {
      
      $bReplace = false;
      
      // Argument is here
      
      if ($mArgument !== null && ($oChild->testAttribute('use-zero', true) || $mArgument !== 0)) {
        
        // Argument has value
        
        $aFormats = array();
        
        if ($sFormat = $oChild->getAttribute('format')) {
          
          $aFormats[] = $sFormat; // TODO NS BUGS
          
        } else if ((!$aFormats = $oChild->query('xhtml:format', array('xhtml' => SYLMA_NS_XHTML))->toArray()) &&
          ($oFormat = $oChild->get('le:formats', 'le', SYLMA_NS_EXECUTION))) {
          
          $aFormats = $oFormat->getChildren()->toArray();
        }
        
        if ($oChild->testAttribute('use-mixed', false)) { // value are replaced instead of validated
          
          if (count($aFormats) > 1) {
            
            $this->log(txt('@attribute %s must have only one format in @element %s', 'use-mixed', $oChild->getPath()), 'warning');
            
          } else {
            
            $bResult = true;
            $sFormat = array_pop($aFormats);
            
            switch ($sFormat) {
              
              case 'php-boolean' :
                
                if (is_string($mArgument)) $mResult = strtobool($mArgument);
                else $mResult = (bool) $mArgument;
                
              break;
              
              case 'php-string' : $mResult = (string) $mArgument; break;
              case 'php-array' : $mResult = array($mArgument); break;
              default :
                
                $this->log(txt('Unknown format %s in @element %s', $sFormat, $oChild->getPath()), 'warning');
                $bResult = false;
            }
            
            $bReplace = true;
          }
          
        } else { // values are validated
          
          if (!$this->validArgumentType($mArgument, $aFormats, $oChild)) {
            
            // dspm(xt('L\'argument "%s" est au mauvais format dans %s !', new HTML_Strong($mKey), $this->getPath()->parse()), 'error');
            $bResult = false;
            
          } else {
            
            // Argument is good format
            
            /* Validation */
            
            if (($oValidate = $oChild->getByName('validate', SYLMA_NS_EXECUTION)) && $oValidate->hasChildren()) {
              
              // pre-set argument result for variable
              
              $this->setVariableElement($oChild, $mArgument);
              
              if (!$mResult = $this->buildArgument($oValidate->getFirst())) {
                
                if ($oValidate->testAttribute('required', true)) {
                  
                  $this->log(txt('Argument %s is invalid', $mKey), 'error');
                }
                
                $bResult = false;
                
              } else {
                
                if ($oValidate->testAttribute('return')) $bReplace = true;
                
                if (Controler::useStatut('action/report')) {
                  
                  $sArgumentType = $bAssoc ? 'assoc' : 'index';
                  dspm(xt('Argument : %s [%s]', view($mArgument), new HTML_Em($sArgumentType)), 'action/report');
                }
              }
            }
          }
        }
      }
      
      /* Default value */
      
      if (($mArgument === null || !$bResult) && ($oDefault = $oChild->get('le:default', 'le', SYLMA_NS_EXECUTION)) && $oDefault->hasChildren()) {
        
        // Argument has no value and is required
        
        if ((!$mResult = $this->buildArgument($oDefault->getFirst())) && $oDefault->testAttribute('required')) {
          
          $this->log(txt('Invalid default value for @element %s', $mKey), 'error');
          $bResult = false;
          
        } else {
          
          if ($oDefault->testAttribute('replace', true)) $bReplace = true;
          $bResult = true;
        }
      }
      
      /* Hypothetical replacement */
      
      if ($bReplace) {
        
        $bResult = true;
        $mTemp = $mArgument;
        $mArgument = $mResult;
        
        if ($bAssoc) $this->getPath()->setAssoc($mKey, $mResult);
        else $this->getPath()->setIndex($mKey, $mResult);
        
        if (Controler::useStatut('action/report')) {
          
          $sArgumentType = $bAssoc ? 'assoc' : 'index';
          dspm(xt('Argument redéfini : %s &gt; %s [%s:%s]', view($mTemp), view($mResult), $sArgumentType, $mKey), 'action/report');
        }
      }
    }
    
    return array($bResult, $mArgument);
  }
  
  private function validArgumentType(&$mArgument, $aFormats, $oElement, $bNull = false) {
    
    // if no format 
    if (!$aFormats || ($mArgument === null && $bNull)) $bResult = true;
    else {
      
      $bResult = false;
      
      if (is_object($mArgument)) {
        
        $sActualFormat = get_class($mArgument);
        foreach ($aFormats as $sFormat) if ($mArgument instanceof $sFormat) {
          
          $bResult = true;
          break;
        }
        
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
        
        if (in_array($sActualFormat, $aFormats)) {
          
          $bResult = true;
          
          if ($sActualFormat == 'php-string') {
            
            if (($sEnum = $oElement->getAttribute('enum')) && !in_array($mArgument, explode(',', $sEnum))) $bResult = false;
          }
        }
      }
    }
    
    if (!$bResult) {
      
      $this->log(txt('Invalid argument %s for @element %s', gettype($mArgument), $oElement->getPath()), 'error');
      $this->setStatut('error');
    }
    
    return $bResult;
  }
  
  public function getSettings() {
    
    return $this->oSettings;
  }
  
  public function loadSettings($oSettings) {
    
    $bResult = true;
    $iArgument = 0;
    
    $this->oSettings = new XML_Document($oSettings);
    
    if ($oSettings && $oSettings->hasChildren()) {
      
      foreach ($oSettings->getChildren() as $oChild) {
        
        switch ($oChild->getName(true)) {
          
          case 'return' : break;
          case 'name' : break;
          case 'argument' :
            
            list($bResult, $mResult) = $this->validateArgument($oChild, $iArgument);
            
            $this->setVariableElement($oChild, $mResult);
            
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
                    new XML_Element('le:self', null, array('return' => 'true'), SYLMA_NS_EXECUTION),
                    array('path' => $sPath), SYLMA_NS_EXECUTION);
                  
                  if (!$oResult = $this->buildArgument($oAction)) {
                    
                    $this->log(txt('Cannot find processor %s', $sNamespace), 'error');
                  }
                  else {
                    
                    $oResult->startAction($this);
                    $this->aProcessors[$sNamespace] = $oResult;
                  }
                }
                
              } else $this->log(txt('No path defined for processor %s', $oChild->getPath()), 'error');
              
            } else $this->log(txt('@attribute %s is missing in @element %s', 'namespace', $oChild->getPath()), 'error');
            
          break;
          
          case 'setting' : // do nothing
          break;
          
          default :
            
            // Just run the element, used for set-variable
            if ($oChild->testAttribute('run', true)) $this->buildArgument($oChild);
        }
        
        if (!$bResult) break;
      }
      
      $oSettings->remove();
    }
    
    return $bResult;
  }
  
  /*** Infos ***/
  
  /**
   * Build action's first element for the infos box
   */
  public function getResume() {
    
    if (!$this->oResume) {
      
      $this->oResume = new XML_Element('action', null, array(
        'path' => $this->getPath()), self::MONITOR_NS);
    }
    
    return $this->oResume;
  }
  
  public function resumeQuery($sQuery) {
    
    if (array_key_exists($sQuery, $this->aQueries)) $this->aQueries[$sQuery]++;
    else $this->aQueries[$sQuery] = 1;
  }
  
  /**
   * Add a file to this action for infos box
   */
  public function resumeFile($oFile, $bFirstTime) {
    
    if (!$oFiles = $this->getResume()->getByName('files')) {
      
      $oFiles = $this->getResume()->addNode('files', null, array(), self::MONITOR_NS);
    }
    
    $oResume = $oFile->parseXML();
    if ($bFirstTime) $oResume->setAttribute('first-time' , 1);
    
    // if ($oResume) dspf($oResume->getNamespace());
    $oFiles->add($oResume);
  }
  
  /**
   * Add a sub-action to this action in infos box
   */
  public function resumeAction($oAction) {
    
    $this->aSubActions[] = $oAction->viewResume();
  }
  
  /**
   * Get the stats resume for infos box
   */
  public function viewResume() {
    
    $oAction = $this->getResume();
    $oStats = $oAction->addNode('stats');
    $oArguments = $oAction->addNode('arguments');
    $oVariables = $oAction->addNode('variables');
    $oQueries = $oAction->addNode('queries');
    
    // build stats
    
    $aStats = array();
    
    foreach ($this->aStats as $sName => $fValue) {
      
      $fValue = float_format($fValue);
      
      $aStats[$sName] = $oStats->addNode('stat', null, array(
        'name' => $sName,
        'value' => $fValue,
        'sub-value' => $fValue));
    }
    
    // build variables
    
    foreach ($this->aVariables as $sKey => $mVar) {
      
      $oVariables->addNode('variable', view($mVar, false), array('name' => $sKey));
    }
    
    // build arguments
    
    if ($this->oPathResume) $oAction->add($this->oPathResume);
    
    // build queries
    
    foreach ($this->aQueries as $sQuery => $iQueries) $oQueries->addNode('query', $sQuery, array('count' => $iQueries));
    
    // build sub-actions
    
    if ($this->aSubActions) {
      
      // add children
      $oSubActions = $oAction->addNode('sub-actions');
      
      foreach ($this->aSubActions as $oSubAction) {
        
        $oTest = $oSubActions->add($oSubAction);
      }
      
      // evaluate stat weight with children's
      foreach ($this->aStats as $sName => $fValue) {
        
        $oSubStats = $oSubActions->query("lem:action/lem:stats/lem:stat[@name='$sName']", $this->aNS);
        
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
  
  public function parse($aStats = array(), $bMessage = true) {
    
    $oResult = null;
    $bStats = false;
    
    // Load stats
    
    if (Sylma::get('actions/stats/enable') && Controler::isAdmin()) {
      
      $bStats = true;
      
      if (!$aStats) {
        
        $aStats = XML_Controler::getStats();
        $aStats['time'] = microtime(true);
      }
      
      Controler::infosOpenAction($this);
      
      // add arguments
      
      if ($this->getPath()) $this->oPathResume = $this->getPath()->viewResume();
    }
    
    // begin check & parsing
    
    // Start processors
    
    if ($this->aProcessors) foreach ($this->aProcessors as $oProcessor) $oProcessor->startAction($this);
    
    try {
      
      if ($this->isEmpty()) {
        
        if ($bMessage) $this->log(txt('Empty document'), 'error');
      }
      else {
        
        $oRoot = $this->getRoot();
        $oDocument = new XML_Document($oRoot);
        
        if (Controler::useStatut('action/report')) {
          
          $oSeek = new HTML_Span(t('>>> Début'), array('style' => 'color: green;'));
          dspm(array(xt('%s de l\'exécution du fichier %s', $oSeek, $this->getPath()->parse()), new HTML_Tag('hr')), 'action/report');
        }
        
        switch ($oRoot->getNamespace()) {
          
          /* Execution */
          
          case SYLMA_NS_EXECUTION :
            
            switch ($oRoot->getName(true)) {
              
              // action
              
              case 'action' :
                
                if ($this->loadSettings($oDocument->getByName('settings', SYLMA_NS_EXECUTION))) {
                  
                  if ($this->getSettings() && !$this->getSettings()->isEmpty()
                    && ($oReturn = $this->getSettings()->getByName('return', SYLMA_NS_EXECUTION)))
                    $sReturn = $oReturn->getAttribute('format');
                  else $sReturn = '';
                  
                  // set default variables
                  
                  $this->setVariables(array(
                    'sylma-user' => Controler::getUser()->getName(),
                    'sylma-directory' => (string) $this->getDirectory(),
                    'sylma-lang' => (string) Controler::getSettings('infos/lang'),
                  ));
                  
                  switch ($sReturn) {
                    
                    case 'XML_Document' :
                      
                      $oResult = new XML_Document($this->buildArgument($oDocument->getFirst()));
                      
                    break;
                    
                    case 'Redirect' :
                    case 'mixed' :
                      
                      $oResult = $this->buildArgument($oDocument->getFirst());
                      // if ($oDocument->countChildren() > 1) $this->dspm(xt('Mode mixed'), 'warning');
                    break;
                    
                    default :
                      
                      $oResult = new XML_Document('temp');
                      
                      $oMethod = new XML_Element('li:add', $oDocument->getRoot()->getChildren(), null, SYLMA_NS_INTERFACE);
                      $this->runInterfaceMethod($oResult, $oMethod, ActionControler::getInterface($oResult, $this->getRedirect()));
                      
                      if (!$oResult->isEmpty()) $oResult = $oResult->getRoot()->getChildren();
                      else $this->log(txt('No value returned'), 'warning');
                  }
                  
                } else {
                  
                  $this->setStatut('error');
                  $this->log(txt('Parsing abort ..'), 'error');
                }
                
              break;
              
              case 'interface' :
                
                if (!$oSettings = $this->getByName('settings', SYLMA_NS_EXECUTION)) {
                  
                  $this->log(txt('@element %s is missing', 'settings'), 'warning');
                  
                } else {
                  
                  if ($oAction = $oSettings->getByName('use-action')) {
                    
                    if (!$sPath = $oAction->getAttribute('path')) {
                      
                      $this->log(txt('@attribute %s is missing in @element %s', 'path', $oAction->getPath()), 'error');
                    }
                    else {
                      
                      $oInterface = new XML_Document($sPath);
                      Action_Builder::buildInterface($oInterface);
                    }
                    
                  } else if ($sClass = $oSettings->readByName('class', SYLMA_NS_EXECUTION)) {
                    
                    $oInterface = ActionControler::getInterface($sClass);
                  }
                  
                  $oSettings->remove();
                  
                  if ($oRoot->hasChildren()) {
                    
                    $aArguments = $this->loadElementArguments($oRoot);
                    $this->getPath()->pushIndex($aArguments['index']);
                    $this->getPath()->mergeAssoc($aArguments['assoc']);
                  }
                  
                  if ($oInterface) {
                    
                    $oResult = $this->loadInterface($oInterface);
                    list($oSubResult, $bSubReturn) = $this->runInterfaceList($oResult, $oRoot);
                  }
                }
                
              break;
              
              default :
                
                $this->log(txt('Invalid root @element %s', $oRoot->getPath()), 'warning');
                
              break;
            }
            
          break;
          
          /* Interface */
          
          case SYLMA_NS_INTERFACE :
            
            $oResult = $this->loadInterface($oRoot);
            
          break;
          
          default :
            
            $this->log(txt('Invalid namespace for @element %s', $oRoot->getPath()), 'warning');
            
          break;
          
        }
        
        if (Controler::useStatut('action/report')) {
          
          $oSeek = new HTML_Span(t('<<< Fin'), array('style' => 'color: red;'));
          dspm(array(xt('%s de l\'exécution du fichier %s', $oSeek,$this->getPath()->parse()), new HTML_Tag('hr')), 'action/report');
        }
        
        if (!$this->getStatut()) $this->setStatut('success');
        if (is_object($oResult) && $oResult instanceof Redirect) {
          
          $this->setStatut('redirect');
          $this->setRedirect($oResult);
        }
      }
    }
    catch (SylmaExceptionInterface $e) {
    
    }
    catch (Exception $e) {
      
      Sylma::loadException($e);
    }
    
    // Stop processors
    
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
        
        //dspm(xt('Action "%s" impossible, argument(s) invalide(s) !', new HTML_Strong($this->getPath())), 'error');
        if (Sylma::get('actions/error/redirect')) Controler::errorRedirect();
        
      break;
      
      default : // Pas de document (404)
        
        if ($this->getPath()) $this->log(txt('Invalid or missing document!'), 'error');
        
      break;
    }
    
    return null;
  }
  
  protected function log($mMessage, $sStatut = Sylma::LOG_STATUT_DEFAULT) {
    
    $aPath = array(
      '@namespace ' . $this->getNamespace(),
      '@file ' . $this->getPath());
    
    return Sylma::throwException($mMessage, $aPath);
  }
}



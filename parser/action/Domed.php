<?php

namespace sylma\parser\action;
use \sylma\core, \sylma\dom, \sylma\parser, \sylma\storage\fs;

require_once('core/module/Controled.php');
require_once('parser/domed.php');

class Domed extends core\module\Controled implements parser\domed {
  
  const PREFIX = 'le';
  const CONTROLER = 'parser/action';
  
  /**
   * See @method setFile()
   * @var storage\fs
   */
  private $document;
  private $aArguments = array();
  
  /**
   * Sub parsers
   * @var array
   */
  private $aParsers = array();
  
  private $window;
  
  public function __construct(core\factory $controler, dom\handler $doc) {
    
    $this->setDocument($doc);
    
    $this->setControler($controler);
    //$this->file = $file;
    $this->setNamespace(self::NS, self::PREFIX);
    
    //$this->setDirectory(__file__);
    
    //$this->setArguments()
    //$this->loadDefaultArguments();
    //dspf($this->getArguments());
  }
  
  protected function setDocument(dom\handler $doc) {
    
    $this->document = $doc;
  }
  
  protected function getDocument() {
    
    return $this->document;
  }
  
  public function setArguments(array $aArguments) {
    
    $this->aArguments = $aArguments;
  }
  
  public function getArguments() {
    
    return $this->aArguments;
  }
  
  public function setWindow(php\Window $window) {
    
    $this->window = $window;
  }
  
  public function getWindow() {
    
    if (!$this->window) {
      
      $this->throwException(t('No window defined'));
    }
    
    return $this->window;
  }
  
  private function getParser($sUri) {
    
    return array_key_exists($sUri, $this->aParsers) ? $this->aParsers[$sUri] : null;
  }
  
  protected function extractArguments(dom\element $settings) {
    
    $aResult = array();
    $args = $settings->query('le:argument');
    
    foreach ($args as $arg) {
      
      
    }
    
    return $aResult;
  }
  
  protected function parseSettings(dom\element $settings) {
    
    foreach ($settings->query() as $el) {
      
      if ($el->getNamespace() == $this->getNamespace()) {
        
        switch ($el->getName()) {
          
          case 'argument' : break;
          case 'name' : $this->setName($el->read());
          
          default : $this->parseElement($el);
        }
      }
      else {
        
        $this->parseElement($el);
      }
    }
  }
  
  protected function parseDocument(dom\document $doc) {
    
    $aResult = array();
    
    if ($doc->isEmpty()) {
      
      $this->throwException(t('empty doc'));
    }
    
    $doc->registerNamespaces($this->getNS());
    
    $settings = $doc->get(self::PREFIX . ':settings', $this->getNS());
    
    // arguments
    
    if ($settings) {
      
      $aArguments = $this->extractArguments($settings);
      $this->parseSettings($settings);
    }
    
    $mResult = $this->parseElement($doc->getRoot());
    
    if (!is_array($mResult)) $aResult = array($mResult);
    else $aResult = $mResult;
    
    return $aResult;
  }
  
  protected function parseNode(dom\node $node) {
    
    $mResult = null;
    
    switch ($node->getType()) {
      
      case dom\node::ELEMENT :
        
        $mResult = $this->parseElement($node);
      
      break;
      
      case dom\node::TEXT :
        
        $mResult = $node->read();
        
      break;
      
      case dom\node::COMMENT :
      
      break;
      
      default : 
        
        $this->throwException(txt('Unknown node type : %s', $node->getType()));
    }
    
    return $mResult;
  }
  
  public function parseElement(dom\element $el) {
    
    $sNamespace = $el->getNamespace();
    $result = null;
    
    if ($sNamespace == $this->getNamespace()) {
      
      $result = $this->parseElementSelf($el);
    }
    else {
      
      $result = $this->parseElementForeign($el);
    }
    
    return $result;
  }
  
  protected function parseElementForeign(dom\element $el) {
    
    if ($parser = $this->getParser($el->getNamespace())) {
      
      $mResult = $parser->parseElement($el);
    }
    else {
      
      $mResult = $el;
    }
    
    return $mResult;
  }
  
  protected function parseElementSelf(dom\element $el) {
    
    $result = null;
    
    switch ($el->getName()) {
      
      case 'action' :
        
        $result = $this->parseElement($el->getFirst());
        
      case 'call' :
        
        
        
      break;
      
      case 'argument' :
      case 'test-argument' :
      case 'get-all-arguments' :
      case 'get-argument' :
      // case 'get-settings' :
      case 'set-variable' :
      case 'get-variable' :
      case 'switch' :
      case 'function' :
      case 'interface' :
      break;
      case 'action' :
      case 'xquery' :
      case 'recall' :
      case 'namespace' :
      case 'ns' :
      case 'php' :
      case 'special' :
      case 'controler' :
        
        $sName = $el->getAttribute('name');
        $result = $window->setControler($sName);
        
      break;
      
      case 'redirect' :
        
        $result = $window->create('call', array($window, $window->getSelf(), 'getRedirect'));
        
      break;
      
  // <object name="window" call="Controler::getWindow()"/>
  // <object name="redirect" call="$oRedirect"/>
  // <object name="user" call="Controler::getUser()"/>
  // <object name="path" call="$oAction-&gt;getPath()" return="true"/>
  // <object name="path-simple" call="$oAction-&gt;getPath()-&gt;getSimplePath()" return="true"/>
  // <object name="path-action" return="true" call="$oAction-&gt;getPath()-&gt;getActionPath()"/>
  // <object name="self" call="$oAction" return="true"/>
  // <object name="directory" call="$oAction-&gt;getPath()-&gt;getDirectory()" return="true"/>
  // <object name="parent-directory" call="$oAction-&gt;getPath()-&gt;getDirectory()-&gt;getParent()" return="true"/>
  // <object name="parent" call="$oAction-&gt;getParent()"/>
  // <object name="database" call="Controler::getDatabase()"/>
      case 'document' :
        
        //if ($el->hasChildren())
        
        $result = $this->reflectDocument($el);
        
      case 'file' : 
        
        $result = $this->reflectFile($el);
        
      break;
        
      case 'template' :
    }
    
    return $result;
  }
  
  protected function reflectFile(dom\element $el) {
    
    $result = null;
    
    if (!$sPath = $el->getAttribute('path')) {
      
      $this->throwException(txt('No path defined for %s', $el->getPath()));
    }
    
    $sPath = core\functions\path\toAbsolute($sPath, $this->getControler()->getDirectory());
    $window = $this->getWindow();
    
    $path = $this->parseString($sPath);
    
    if (!$sOutput = $el->getAttribute('output')) $sOutput = 'xml';
    if (!$iMode = (int) $el->getAttribute('mode')) $iMode = \Sylma::MODE_READ;
    
    require_once('core/functions/Text.php');
    
    $bParse = strtobool($el->getAttribute('parse-variables'));
    
    if ($bParse) {
      
      $result = $window->create('template', array($this->parseString($callContent, true)));
    }
    else {
      
      $result = $window->create('call', array($window, $window->getSelf(), 'parseFile', array($path, $iMode, $sOutput, $bParse)));;
    }
    
    return $result;
  }
  
  protected function reflectDocument(dom\element $el) {
    
    
  }
  
  protected function parseAttributes(dom\element $el) {
  
  }
  
  protected function parseString($sValue) {
    
    $window = $this->getWindow();
    
    preg_match_all('/\[\$([\w-]+)\]/', $sValue, $aResults, PREG_OFFSET_CAPTURE);
    
    if ($aResults && $aResults[0]) {
      
      $iSeek = 0;
      
      foreach ($aResults[1] as $aResult) {
        
        $iVarLength = strlen($aResult[0]) + 3;
        $sVarValue = (string) $window->getVariable($aResult[0]);
        
        $sValue = substr($sValue, 0, $aResult[1] + $iSeek - 2) . $sVarValue . substr($sValue, $aResult[1] + $iSeek - 2 + $iVarLength);
        
        $iSeek += strlen($sVarValue) - $iVarLength;
      }
    }
    
    return $window->create('string', array($sValue));
  }
  
  public function asDOM() {
    
    $window = $this->getControler()->create('window', array($this->getControler()));
    $this->setWindow($window);
    
    $doc = $this->getDocument();
    $window = $this->getWindow();
    
    if ($aResult = $this->parseDocument($doc)) $window->setContent($aResult);
    
    //dspf($aResult);
    
    $arg = $window->asArgument();
    //$tst = $arg->get('window')->query();
    //dspm((string) $tst[1]);
    return $arg->asDOM();
  }
}

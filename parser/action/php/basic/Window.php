<?php

namespace sylma\parser\action\php\basic;
use \sylma\parser\action, \sylma\core, \sylma\dom, \sylma\parser\action\php;

require_once('core/module/Argumented.php');
require_once(dirname(__dir__) . '/window.php');
require_once('core/controled.php');

class Window extends core\module\Filed implements php\window, core\controled {
  
  const NS = 'http://www.sylma.org/parser/action/compiler';
  const ARGUMENTS_PATH = 'classes/php';
  
  // Keyed by alias. ex : storage/fs
  private $aControlers = array();
  
  // Keyed by namespace. ex : http://www.sylma.org/parser/action
  private $aParsers = array();
  
  // Keyed by file path. ex : #sylma/action/index.xsl
  private $aDependencies = array();
  
  // Indexed
  private $aContent = array();
  
  // $this reference object
  private $self;
  
  // static reference to class
  private $sylma;
  
  protected static $varCount = 0;
  
  public function __construct(core\factory $controler) {
    
    $this->setControler($controler);
    $this->setArguments($controler->getArgument(self::ARGUMENTS_PATH));
    $this->setNamespace(self::NS);
    
    $self = $this->loadInstance('\sylma\parser\action\Basic');
    
    $this->self = $this->create('object-var', array($self, 'this'));
    $this->setScope($this);
    //$this->sylma = $this->create('class-static', array('\Sylma'));
  }
  
  public function createArgument(array $aArguments, $sNamespace = '') {
    
    return parent::createArgument($aArguments, $sNamespace);
  }
  
  public function setControler(core\factory $controler) {
    
    $this->controler = $controler;
  }
  
  public function getControler() {
    
    return $this->controler;
  }
  
  public function setContent(array $aContent) {
    
    $this->aContent = $aContent;
  }
  
  public function add($mVal) {
    //dspf($mVal);
    if (is_array($mVal)) $this->aContent = array_merge($this->aContent, $mVal);
    else $this->aContent[] = $mVal;
    //dspf($this->aContent);
  }
  
  public function addControler($sName) {
    
    $this->aControlers[] = $sName;
  }
  
  public function getSelf() {
    
    return $this->self;
  }
  
  public function getSylma() {
    
    return $this->sylma;
  }
  
  public function getVarName() {
    
    self::$varCount++;
    return 'var' . self::$varCount;
  }
  
  public function createCall(php\_object $obj, $sMethod, $mReturn, array $aArguments = array()) {
    
    if (is_string($mReturn)) {
      
      $return = $this->loadInstance($mReturn);
    }
    else {
      
      $return = $mReturn;
    }
    
    $result = $this->create('call', array($this, $obj, $sMethod, $return, $aArguments));
    
    return $result;
  }
  
  public function loadInstance($sName, $sFile = '') {
    
    $result = null;
    
    if (substr($sName, 0, 4) == 'php:') {
      
      switch(substr($sName, 4)) {
        
        case 'string' :
        case 'array' :
        case 'boolean' :
        case 'integer' :
        default :
          
          $this->throwException(txt('Unknown php type %s', $this->getReturn()));
      }
    }
    else {
      
      $interface = $this->create('interface', array($this, $sName, $sFile));
      $result = $this->create('object', array($this, $interface));
    }
    
    return $result;
  }
  
  public function createInsert(core\argumentable $val) {
    
    //if ($val instanceof dom\node) $result = $val;
    $result = $this->create('insert', array($this, $val));
    
    return $result;
  }
  
  public function setScope(php\scope $scope) {
    
    $this->scope = $scope;
  }
  
  public function addScope($val) {
    
    $this->scope->add($val);
  }
  
  public function parseArgument($mVar) {
    
    $arg = null;
    
    switch (gettype($mVar)) {
      
      case 'boolean' :
        
        $arg = $this->create('boolean', array($mVar));
        
      break;
      
      case 'integer' :
      case 'double' :
        
        $arg = $this->create('numeric', array($mVar));
      
      break;
      
      case 'string' :
        
        $arg = $this->create('string', array($mVar));
        
      break;
      
      case 'array' :
        
        $arg = $this->create('array', array($mVar));
        
      break;
      
      case 'object' :
        
        if ($mVar instanceof php\_object || $mVar instanceof php\scalar || $mVar instanceof dom\node) $arg = $mVar;
        else $arg = $this->create('object', array($this, $mVar));
        
      break;
      
      case 'resource' :
      case 'NULL' :
    }
    
    return $arg;
  }
  
  public function asArgument() {
    
    $aControlers = array();
    
    foreach ($this->aControlers as $sControler) {
      
      $sName = '$controler' . ucfirst(str_replace('/', '_', $sControler));
      
      // $controlerXX_X = \Sylma::getControler('xx/x');
      
      $var = $this->create('var', array($sName));
      $call = $this->create('call-static', array($this->getSylma(), 'getControler', array($sControler)));
      
      $aControlers[] = $this->create('assign', array($var, $call));
    }
    
    $result = $this->createArgument(array(
      'window' => array(
        $aControlers,
      ),
    ), self::NS);
    
    $result->get('window')->mergeArray($this->aContent);
    
    return $result;
  }
}
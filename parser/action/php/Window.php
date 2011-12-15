<?php

namespace sylma\parser\action\php;
use \sylma\parser\action, \sylma\core;

require_once('core/module/Argumented.php');
require_once('core/controled.php');
require_once('core/argumentable.php');

class Window extends core\module\Filed implements core\argumentable, core\controled {
  
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
  
  public function __construct(core\factory $controler) {
    
    $this->setControler($controler);
    $this->setArguments($controler->getArgument(self::ARGUMENTS_PATH));
    
    $self = $this->create('object', array('action\cached'));
    
    $this->self = $this->create('object-var', array($self, 'this'));
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
    
    $this->aContent[] = $mVal;
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
      case 'resource' :
        
        $arg = $mVar;
        
      break;
      
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
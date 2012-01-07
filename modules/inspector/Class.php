<?php

use \sylma\core;

require_once('ReflectorInterface.php');
require_once('ReflectorCommented.php');

class InspectorClass extends InspectorReflectorCommented implements InspectorReflectorInterface {
  
  const EXCEPTION_NO_SOURCE = 'No file source found';
  const CONSTANT_CLASS = 'class/constant';
  const PROPERTY_CLASS = 'class/property';
  const METHOD_CLASS = 'class/method';
  
  protected $controler;
  
  /**
   * File where is located the class
   */
  protected $file;
  
  protected $aSource;
  protected $iOffset;
  protected $sExtends = '';
  
  protected $aConstants = array();
  protected $aProperties = array();
  protected $aMethods = array();
  
  protected $extends;
  protected $aInterfaces = array();
  
  /**
   * The string between the declaration of class and the first method containing the properties and constants
   * NULL means it has never been load, '' means nothing found
   */
  protected $sSourceProperties = null;
  
  /**
   * @param string|ReflectorInterface $class The reflector to link to this class 
   * @param $controler The parent element, eg. The module's class
   */
  public function __construct($mClass, ModuleBase $controler, array $aArguments = array()) {
    
    $this->setArguments($aArguments);
    $this->controler = $controler;
    
    if (is_string($mClass)) $this->reflector = new ReflectionClass($mClass);
    else $this->reflector = $mClass;
    
    if (!$this->getReflector()) $this->throwException('No reflector has been defined');
    
    if ($this->getReflector()->isUserDefined()) {
      
      $this->loadFile();
      $this->loadParents();
    }
    else {
      
      $this->loadSytemParents();
    }
    
    $this->loadConstants();
    $this->loadMethods();
    $this->loadProperties();
    $this->loadComment('class/comment');
  }
  
  protected function getControler() {
    
    return $this->controler;
  }
  
  public function getSource($bText = false) {
    
    if ($bText) return implode('', $this->aSource);
    else return $this->aSource;
  }
  
  public function getSourceProperties() {
    
    if ($this->getReflector()->isUserDefined() && $this->sSourceProperties === null) {
      
       $this->sSourceProperties = '';
      
      $sName = $this->getName();
      
      preg_match("/{([^{]*)function/", $this->getSource(true), $aResult);
      
      if (!$aResult || empty($aResult[1])) {
        // dspf($this->getSource(true));
        // $this->throwException(t('No properties found'));
      }
      else {
        
        $this->sSourceProperties = $aResult[1];
      }
    }
    
    return $this->sSourceProperties;
  }
  
  public function getOffset() {
    
    return $this->iOffset;
  }
  
  protected function loadFile() {
    
    if ($sFile = $this->getReflector()->getFileName()) {
      
      require_once('core/functions/Path.php');
      
      $sFile = core\functions\path\winToUnix($sFile);
      
      $sDirectory = Controler::getDirectory()->getSystemPath();
      $file = Controler::getFile(substr($sFile, strlen($sDirectory) + 1));
      
      $this->file = $file;
      $aSource = $file->readArray();
      
      $iStart = $this->getReflector()->getStartLine() - 1;
      $iEnd = $this->getReflector()->getEndLine();
      
      if (count($aSource) < $iEnd) {
        
        $this->throwException('Cannot load source code, Line end is bigger than file length');
      }
      
      $this->aSource = array_slice($aSource, $iStart, $iEnd - $iStart);
      
      $this->iOffset = $iStart;
    }
  }
  
  public function getError() {
    
    return 'InspectorException';
  }
  
  protected function loadConstants() {
    
    foreach ($this->getReflector()->getConstants() as $sName => $sValue) {
      
      if ($this->getReflector()->hasConstant($sName)) {
        
        $this->aConstants[] = $this->getControler()->create(self::CONSTANT_CLASS, array(
          $sName, $sValue, $this));
      }
    }
  }
  
  protected function loadProperties() {
    
    foreach ($this->getReflector()->getProperties() as $property) {
      
      if ($this->getArgument('no-private', false) && $property->isPrivate()) continue;
      
      if ($property->getDeclaringClass() == $this->getReflector()) {
        
        $this->aProperties[] = $this->getControler()->create(self::PROPERTY_CLASS, array(
          $property, $this));
      }
    }
  }
  
  protected function loadMethods() {
    
    foreach ($this->getReflector()->getMethods() as $method) {
      
      if ($this->getArgument('no-private', false) && $method->isPrivate()) continue;
      
      if ($method->getDeclaringClass() == $this->getReflector()) {
        
        $this->aMethods[] = $this->getControler()->create(
          self::METHOD_CLASS,
          array($method, $this));
      }
    }
  }
  
  /**
   * Load parent classes (extends & implements)
   */
  protected function loadSytemParents() {
    
    if ($class = $this->getReflector()->getParentClass()) {
      
      if ($this->getArgument('parent', true)) {
        
        $this->extends = $this->getControler()->create('class', array($class, $this->getControler(), array('no-private' => true)));
      }
      else $this->extends = $class->getName();
    }
  }
  
  protected function loadParents() {
    
    if (!$aSource = $this->getSource()) {
      
      $this->throwException(self::EXCEPTION_NO_SOURCE);
    }
    
    preg_match('/' . $this->getName() . '(?:\s+extends\s+(?P<extends>\w+))?(?:\s+implements\s+(?P<implements>[\w,\s]+))?/', $aSource[0], $aMatch);
    
    if (!empty($aMatch['extends'])) {
      
      if ($this->getArgument('parent', true)) {
        
        $this->extends = $this->getControler()->create('class', array($aMatch['extends'], $this->getControler(), array('no-private' => true)));
      }
      else $this->extends = $aMatch['extends'];
    }
    
    if (!empty($aMatch['implements'])) {
      
      $this->aInterfaces = array_map('trim', explode(',', $aMatch['implements']));
    }
  }
  
  public function throwException($sMessage, $mSender = array(), $iOffset = 2) {
    
    $mSender = (array) $mSender;
    array_push($mSender,
      '@class ' . $this->getName(),
      '@file ' . $this->file);
    
    return parent::throwException($sMessage, $mSender, $iOffset);
  }
  
  public function parse() {
    
    $node = Arguments::buildFragment(array(
      'class' => array( 
        '@name' => $this->getName(),
        '@file' => (string) $this->file,
        'extension' => $this->extends,
        'package' => $this->getReflector()->isUserDefined() ? 'php' : '', 
        $this->comment,
        $this->aConstants,
        $this->aProperties,
        $this->aMethods,
      ),
    ), $this->getControler()->getNamespace());
    
    if ($this->aInterfaces && $node->getFirst()) {
      
      foreach ($this->aInterfaces as $sInterface)
        $node->getFirst()->addNode('interface', $sInterface);
    }
    
    return new XML_Document($node);
  }
  
  public function __toString() {
    
    return implode(' ', Reflection::getModifierNames($this->getReflector()->getModifiers())) .
      'class ' . $this->getName() .
      ($this->sExtends ? ' extends ' . $this->sExtends : '') .
      ($this->aInterfaces ? ' implements ' . implode(', ', $this->aInterfaces) : '') . " {\n\n" .
      implode("\n", $this->aConstants) .
      ($this->aConstants ? "\n\n" : '') .
      implode("\n", $this->aProperties) .
      ($this->aProperties ? "\n\n" : '') .
      implode("\n\n", $this->aMethods) .
      "\n}";
  }
}

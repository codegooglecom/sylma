<?php

class InspectorClass extends InspectorReflector implements InspectorReflectorInterface {
  
  const EXCEPTION_NO_SOURCE = 'No file source found';
  const CONSTANT_CLASS = 'constant';
  const PROPERTY_CLASS = 'property';
  const METHOD_CLASS = 'method';
  
  /* File where is located the class */
  protected $controler;
  protected $file;
  
  protected $aSource;
  protected $iOffset;
  
  protected $aConstants = array();
  protected $aProperties = array();
  protected $aMethods = array();
  
  protected $extends;
  protected $aInterfaces = array();
  
  /**
   * An object reading the comment of the class
   * @var InspectorCommentInterface
   */
  protected $comment;
  
  /**
   * The string between the declaration of class and the first method
   * containing the properties and constants
   * NULL means it has never been load, '' means nothing found
   */
  protected $sSourceProperties = null;
  
  /**
   * @param $class The reflector to link to this class 
   * @param $controler The parent element, eg. The module's class 
   */
  public function __construct($mClass, ModuleBase $controler) {
    
    $this->controler = $controler;
    
    if (is_string($mClass)) $this->reflector = new ReflectionClass($mClass);
    else $this->reflector = $mClass;
    
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
  }
  
  protected function getControler() {
    
    return $this->controler;
  }
  
  public function getSource($bText = false) {
    
    if ($bText) return implode('', $this->aSource);
    else return $this->aSource;
  }
  
  public function getSourceProperties() {
    
    if ($this->sSourceProperties === null) {
      
       $this->sSourceProperties = '';
      
      $sName = $this->getName();
      
      preg_match("/{([^{]*)function/", $this->getSource(true), $aResult);
      
      if (!$aResult || empty($aResult[1])) {
        
        $this->throwException(t('No properties found'));
      }
      
      $this->sSourceProperties = $aResult[1];
    }
    
    return $this->sSourceProperties;
  }
  
  public function getOffset() {
    
    return $this->iOffset;
  }
  
  protected function loadFile() {
    
    if ($sFile = $this->getReflector()->getFileName()) {
      
      $sFile = pathWin2Unix($sFile);
      
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
      
      $this->aConstants[] = $this->getControler()->create(self::CONSTANT_CLASS, array(
          $sName, $sValue, $this));
    }
  }
  
  protected function loadProperties() {
    
    foreach ($this->getReflector()->getProperties() as $property) {
      
      if ($property->getDeclaringClass() == $this->getReflector()) {
        
        $this->aProperties[] = $this->getControler()->create(self::PROPERTY_CLASS, array(
          $property, $this));
      }
    }
  }
  
  protected function loadMethods() {
    
    foreach ($this->getReflector()->getMethods() as $method) {
      
      if ($method->getDeclaringClass() == $this->getReflector()) {
        
        $this->aMethods[] = $this->getControler()->create(
          self::METHOD_CLASS,
          array($method, $this));
      }
    }
  }
  
  protected function loadSytemParents() {
    
    if ($class = $this->getReflector()->getParentClass()) {
      
      $this->extends = $this->getControler()->create('class',
        array($class, $this->getControler()));
    }
  }
  
  protected function loadParents() {
    
    if (!$aSource = $this->getSource()) {
      
      $this->throwException(self::EXCEPTION_NO_SOURCE);
    }
    
    preg_match('/' . $this->getName() . '(?:\s+extends\s+(?P<extends>\w+))?(?:\s+implements\s+(?P<implements>[\w,\s]+))?/', $aSource[0], $aMatch);
    
    if (!empty($aMatch['extends'])) {
      
      $this->extends = $this->getControler()->create('class', 
        array($aMatch['extends'], $this->getControler()));
    }
    
    if (!empty($aMatch['implements'])) {
      
      $this->aInterfaces = array_map('trim', explode(',', $aMatch['implements']));
    }
  }
  
  protected function loadComment() {
    
    $this->comment = $this->getControler()->create('comment', $this->getReflector()->getDocComment());
  }
  
  public function throwException($sMessage, $mSender = array()) {
    
    $mSender = (array) $mSender;
    array_push($mSender,
      '@class ' . $this->getName(),
      '@file ' . $this->file);
    
    return parent::throwException($sMessage, $mSender);
  }
  
  public function parse() {
    
    $node = Arguments::buildDocument(array(
      'class' => array( 
        '@name' => $this->getName(),
        'extension' => $this->extends,
        'package' => $this->getReflector()->isUserDefined() ? 'php' : '', 
        //'comment' => $this->comment->getClass(),
        $this->aConstants,
        $this->aProperties,
        $this->aMethods,
      ),
    ), $this->getControler()->getNamespace());
    
    if ($this->aInterfaces) {
      
      foreach ($this->aInterfaces as $sInterface)
        $node->addNode('interface', $sInterface);
    }
    
    return $node;
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

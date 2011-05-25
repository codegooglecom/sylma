<?php

class InspectorClass extends InspectorReflector implements InspectorReflectorInterface {
  
  protected $aProperties = array();
  const PROPERTY_CLASS = 'property';
  
  protected $aMethods = array();
  const METHOD_CLASS = 'method';
  
  /* File where is located the class */
  protected $file;
  
  protected $aSource;
  protected $iOffset;
  
  /**
   * NULL means it has never been load, '' means nothing found
   */
  protected $sSourceProperties = null;
  
  public function __construct(ReflectionClass $class, ModuleBase $controler) {
    
    $this->controler = $controler;
    $this->reflector = $class;
    
    $this->loadFile();
    
    $this->loadMethods();
    $this->loadProperties();
  }
  
  public function getSource($bText = false) {
    
    if ($bText) return implode('', $this->aSource);
    else return $this->aSource;
  }
  
  public function getSourceProperties() {
    
    if ($this->sSourceProperties === null) {
      
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
        
        $this->throw('Cannot load source code, Line end is bigger than file length');
      }
      
      $this->aSource = array_slice($aSource, $iStart, $iEnd - $iStart);
      $this->iOffset = $iStart;
    }
  }
  
  public function getError() {
    
    return 'InspectorException';
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
        
        $this->aMethods[$method->getName()] = $this->getControler()->create(self::METHOD_CLASS, array(
          $method, $this));
      }
    }
  }
  
  public function log($mPath, $sMessage) {
    
    $mPath = (array) $mPath;
    array_push($mPath, '@class ' . $this->getName(), '@file ' . $this->file);
    
    return parent::log($mPath, $sMessage);
  }
  
  public function parse() {
    
    $aContent = $this->aProperties + $this->aMethods;
    
    return new XML_Element('class', $aContent, array(
      'name' => $this->getName()), $this->getControler()->getNamespace());
  }
  
  public function __toString() {
    
    $sExtension = ($parent = $this->getReflector()->getParentClass()) ?
      $parent->getName() : '';
    
    // return self::export($this, false);
    
    return implode(' ', Reflection::getModifierNames($this->getReflector()->getModifiers())) .
      'class ' . $this->getName() .
      ($sExtension ? ' extends ' . $sExtension : '') . " {\n\n" .
      implode("\n", $this->aProperties) .
      "\n\n" .
      implode("\n\n", $this->aMethods) .
      "\n}";
  }
}

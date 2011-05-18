<?php

class InspectorClass extends InspectorReflector implements InspectorReflectorInterface {
  
  protected $aProperties = array();
  const PROPERTY_CLASS = 'property';
  
  protected $aMethods = array();
  const METHOD_CLASS = 'method';
  
  protected $file;
  protected $sSource;
  /**
   * NULL mean it has never been load, '' means nothing found
   */
  protected $sSourceProperties = null;
  
  public function __construct(ReflectionClass $class, ModuleBase $controler) {
    
    $this->controler = $controler;
    $this->reflector = $class;
    
    $this->loadFile();
    
    $this->loadMethods();
    $this->loadProperties();
  }
  
  public function getSourceProperties() {
    
    if ($this->sSourceProperties === null) {
      
      $sName = $this->getReflector()->getName();
      
      preg_match("/class {$sName}[\s\w\/]*{([^{]*)function/", implode('', $this->getSource()), $aResult);
      // dspf($aResult);
      if (!$aResult || empty($aResult[1])) {
        
        $this->sSourceProperties = '';
        $this->log(t('No properties found'));
      }
      else {
        
        $this->sSourceProperties = $aResult[1];
      }
    }
    
    return $this->sSourceProperties;
  }
  
  public function getSource() {
    
    return $this->sSource;
  }
  
  protected function loadFile() {
    
    if ($sFile = $this->getReflector()->getFileName()) {
      
      $sFile = pathWin2Unix($sFile);
      $sDirectory = Controler::getDirectory()->getSystemPath();
      
      $file = Controler::getFile(substr($sFile, strlen($sDirectory) + 1));
      
      $this->file = $file;
      $this->sSource = $file->readArray();
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
        
        $this->aMethods[$method->getName()] = $this->getControler()->create(self::METHOD_CLASS, array(
          $method, $this));
      }
    }
  }
  
  public function log($sMessage) {
    
    return parent::log(xt('@class %s in @file %s : %s',
      $this->getReflector()->getName(),
      $this->file,
      $sMessage));
  }
  
  public function parse() {
    
    $aContent = $this->aProperties + $this->aMethods;
    
    return new XML_Element('class', $aContent, array(
      'name' => $this->getReflector()->getName()), $this->getControler()->getNamespace());
  }
  
  public function __toString() {
    
    $sExtension = ($parent = $this->getReflector()->getParentClass()) ?
      $parent->getName() : '';
    
    // return self::export($this, false);
    
    return implode(' ', Reflection::getModifierNames($this->getReflector()->getModifiers())) .
      'class ' . $this->getReflector()->getName() .
      ($sExtension ? ' extends ' . $sExtension : '') . " {\n\n" .
      implode("\n", $this->aProperties) .
      "\n\n" .
      implode("\n\n", $this->aMethods) .
      "\n}";
  }
}

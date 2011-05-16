<?php

class InspectorClass extends InspectorReflector implements InspectorReflectorInterface {
  
  protected $aProperties = array();
  const PROPERTY_CLASS = 'property';
  
  protected $aMethods = array();
  const METHOD_CLASS = 'method';
  
  protected $file;
  protected $sSource;
  
  public function __construct(ReflectionClass $class, ModuleBase $controler) {
    
    $this->controler = $controler;
    $this->reflector = $class;
    
    $this->loadFile();
    
    $this->loadMethods();
    $this->loadProperties();
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

<?php

class InspectorParameter extends InspectorReflector implements InspectorReflectorInterface {
  
  protected $method;
  
  public function __construct(ReflectionParameter $reflector, InspectorReflectorInterface $method) {
    
    $this->method = $method;
    $this->reflector = $reflector;
  }
  
  protected function getMethod() {
    
    return $this->class;
  }
  
  protected function getControler() {
    
    return $this->getClass()->getControler();
  }
  
  public function parse() {
    
    $aAttr = array(
      'name' => $this->getReflector()->getName()
    );
    
    if ($this->getReflector()->isOptional())
      $aAttr['default'] = $this->getReflector()->getDefaultValue();
    
    return new XML_Element('parameter', null, $aAttr, $this->getControler()->getNamespace());
  }
  
  public function __toString() {
    
    $mDefault = '';
    
    if ($this->getReflector()->isDefaultValueAvailable()) {
      
      $mDefault = $this->getReflector()->getDefaultValue();
      // dspf($mDefault);
      // dspm(gettype($mDefault));
      
      switch (gettype($mDefault)) {
        
        case 'string' : $mDefault = $mDefault ? addQuote($mDefault) : "''"; break;
        case 'integer' : break;
        case 'boolean' : $mDefault = strtoupper(booltostr($mDefault)); break;
      }
      
      // dspm($mDefault);
      // dspm('-----');
      $mDefault = ' = '.$mDefault;
    }
    
    $class = $this->getReflector()->getClass();
    
    return
      ($this->getReflector()->isArray() ? 'array ' : '') .
      ($class ? $class->getName() . ' ' : '') .
      ($this->getReflector()->isPassedByReference() ? '&' : '') .
      '$' . $this->getReflector()->getName() .
      $mDefault;
  }
}

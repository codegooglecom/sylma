<?php

class InspectorProperty extends InspectorReflector implements InspectorReflectorInterface {
  
  protected $parent;
  
  public function __construct(ReflectionProperty $reflector, InspectorReflectorInterface $parent) {
    
    $this->parent = $parent;
    $this->reflector = $reflector;
  }
  
  protected function getParent() {
    
    return $this->parent;
  }
  
  protected function getControler() {
    
    return $this->getParent()->getControler();
  }
  
  public function parse() {
    
    $aAttr = array(
      'name' => $this->getReflector()->getName()
    );
    
    // $aAttr['default'] = $this->getReflector()->getDefaultValue();
    
    return new XML_Element('property', null, $aAttr, $this->getControler()->getNamespace());
  }
  
  public function display() {
    
    $mDefault = '';
    
    if (0 && $this->getReflector()->isDefault()) {
      
      switch (gettype($mDefault)) {
        
        case 'string' : $mDefault = $mDefault ? addQuote($mDefault) : "''"; break;
        case 'integer' : break;
        case 'boolean' : $mDefault = strtoupper(booltostr($mDefault)); break;
      }
      
      $mDefault = ' = '.$mDefault;
    }
    
    return
      '  ' . implode(' ', Reflection::getModifierNames($this->getReflector()->getModifiers())) .
      ' $' . $this->getReflector()->getName() .
      $mDefault . ';';
  }
  
  public function __toString() {
    
    return $this->display();
  }
}

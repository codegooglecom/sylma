<?php

class InspectorMethod extends InspectorReflector implements InspectorReflectorInterface {
  
  protected $class;
  protected $aParameters = array();
  
  public function __construct(ReflectionMethod $reflector, InspectorReflectorInterface $class) {
    
    $this->class = $class;
    $this->reflector = $reflector;
    
    $this->loadParameters();
  }
  
  protected function loadParameters() {
    
    foreach ($this->getReflector()->getParameters() as $parameter) {
      
      $this->aParameters[] = $this->getControler()->create('parameter', array($parameter, $this));
    }
  }
  
  protected function getClass() {
    
    return $this->class;
  }
  
  protected function getControler() {
    
    return $this->getClass()->getControler();
  }
  
  protected function getSource() {
    
    $iStart = $this->getReflector()->getStartLine();
    $iLength = $this->getReflector()->getEndLine() - $iStart - 1;
    
    return implode('', array_slice($this->getClass()->getSource(), $iStart, $iLength));
  }
  
  public function parse() {
    
    $result = new XML_Element('method', null, array(
      'name' => $this->getReflector()->getName()), $this->getControler()->getNamespace());
    
    $result->addNode('source', $this->getSource());
    $result->addNode('comments', $this->getReflector()->getDocComment());
    
    return $result;
  }
  
  public function __toString() {
    
    return
      '  ' . implode(' ', Reflection::getModifierNames($this->getReflector()->getModifiers())) .
      ' function ' . $this->getReflector()->getName() .
      '(' . implode(', ', $this->aParameters) . ") {\n" .
      $this->getSource() .
      "  }";
  }
}

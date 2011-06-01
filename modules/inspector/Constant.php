<?php

class InspectorConstant extends InspectorReflector implements InspectorReflectorInterface {
  
  protected $parent;
  protected $sName;
  
  protected $sValue;
  protected $sDefault;
  
  public function __construct($sName, $sValue, InspectorReflectorInterface $parent) {
    
    $this->parent = $parent;
    
    $this->sName = $sName;
    $this->sValue = $sValue;
    $this->load();
  }
  
  protected function getName() {
    
    return $this->sName;
  }
  
  protected function load() {
    
    $sSource = $this->getParent()->getSourceProperties();
    
    preg_match('/const ' . $this->getName() . '\s*=\s*([^;]+);/', $sSource, $aMatch);
    
    if (!empty($aMatch[1])) $this->sDefault = $aMatch[1];
  }
  
  protected function getParent() {
    
    return $this->parent;
  }
  
  protected function getControler() {
    
    return $this->getParent()->getControler();
  }
  
  public function parse() {
    
    if (!$this->sDefault) {
      
      // no default means it belongs to another class
      return null;
    }
    else {
      
      return Arguments::buildDocument(array(
        'constant' => array(
          '@name' => $this->getName(),
          'default' => $this->sDefault,
          'value' => $this->sValue,
        ),
      ), $this->getControler()->getNamespace());
    }
  }
  
  public function __toString() {
    
    return '  const ' . $this->getName() . ' = ' . $this->sDefault . ';';
  }
}

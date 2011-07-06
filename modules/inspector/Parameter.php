<?php

require_once('ReflectorInterface.php');
require_once('Reflector.php');

class InspectorParameter extends InspectorReflector implements InspectorReflectorInterface {
  
  protected $parent;
  
  protected $sCast;
  protected $sDefault;
  protected $sValue;
  
  public function __construct(ReflectionParameter $reflector, InspectorReflectorInterface $parent) {
    
    $this->parent = $parent;
    $this->reflector = $reflector;
    
    if ($this->getParent()->getReflector()->isUserDefined()) {
      
      $this->load();
    }
  }
  
  protected function getControler() {
    
    return $this->getParent()->getControler();
  }
  
  protected function load() {
    
    // value
    $this->sValue = $this->getReflector()->isOptional() ?
      $this->getReflector()->getDefaultValue() :
      '';
    
    // default  
    
    $sSource = $this->getParent()->getSourceParameters();
    
    preg_match('/(\w*)\s*&?\$' . $this->getName() . '(?:\s*=\s*([^,\)\(]*))?/', $sSource, $aMatch);
    
    if (!empty($aMatch[1])) $this->sCast = $aMatch[1];
    
    if (!empty($aMatch[2])) {
      
      $sDefault = $aMatch[2];
      if ($sDefault == 'array') $sDefault = 'array()';
      
      $this->sDefault = $sDefault;
    }
  }
  
  public function parse() {
    
    return Arguments::buildFragment(array(
      'parameter' => array(
        '@name' => $this->getName(),
        'cast' => $this->sCast,
        'default' => $this->sDefault,
        'value' => $this->sValue,
      ),
    ), $this->getControler()->getNamespace());
  }
  
  public function __toString() {
    
    return
      ($this->sCast ? $this->sCast . ' ' : '') .
      ($this->getReflector()->isPassedByReference() ? '&' : '') .
      '$' . $this->getReflector()->getName() .
      ($this->sDefault ? ' = ' . $this->sDefault : '');
  }
}

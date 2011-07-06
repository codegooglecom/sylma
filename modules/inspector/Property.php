<?php

require_once('ReflectorInterface.php');
require_once('ReflectorCommented.php');

class InspectorProperty extends InspectorReflectorCommented implements InspectorReflectorInterface {
  
  protected $parent;
  
  protected $sDefault;
  
  public function __construct(ReflectionProperty $reflector, InspectorReflectorInterface $parent) {
    
    $this->parent = $parent;
    $this->reflector = $reflector;
    
    $this->load();
    $this->loadComment('class/property/comment');
  }
  
  protected function load() {
    
    $sSource = $this->getParent()->getSourceProperties();
    
    preg_match('/\$' . $this->getName() . '\s*=\s*([^;]+);/', $sSource, $aMatch);
    
    if ($aMatch && !empty($aMatch[1])) $this->sDefault = $aMatch[1];
  }
  
  public function parse() {
    
    return Arguments::buildFragment(array(
      'property' => array(
        '@name' => $this->getName(),
        '@access' => $this->getAccess(),
        'modifiers' => $this->getReflector()->getModifiers(),
        'default' => $this->sDefault,
        $this->comment,
      ),
    ), $this->getControler()->getNamespace());
  }
  
  public function __toString() {
    
    $sComment = $this->getReflector()->getDocComment();
    $aModifiers = Reflection::getModifierNames($this->getReflector()->getModifiers());
    
    return
      ($sComment ? "\n  " . $sComment . "\n" : '') .
      '  ' . implode(' ', $aModifiers) .
      ' $' . $this->getReflector()->getName() .
      ($this->sDefault ? ' = ' . $this->sDefault : '') . ';';
      }
}

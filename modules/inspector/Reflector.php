<?php

require_once('core/module/Base.php');
require_once('ReflectorInterface.php');

abstract class InspectorReflector extends ModuleBase {
  
  protected $reflector;
  protected $parent;
  
  protected function getReflector() {
    
    return $this->reflector;
  }
  
  protected function getControler() {
    
    return $this->getParent()->getControler();
  }
  
  public function getParent() {
    
    return $this->parent;
  }
  
  protected function getAccess() {
    
    return $this->getReflector()->isPublic() ?
      'public' : (
        $this->getReflector()->isPrivate() ?
          'private' :
          'protected');
  }
  
  protected function getName() {
    
    return $this->getReflector() ? $this->getReflector()->getName() : null;
  }
  
  protected function throwException($sMessage, $mSender = array(), $iOffset = 1) {
  	
    $mSender = (array) $mSender;
    
    Sylma::throwException($sMessage, $mSender, $iOffset);
  }
}

<?php

class InspectorMethod extends InspectorReflector implements InspectorReflectorInterface {
  
  protected $class;
  protected $aParameters = array();
  
  /**
   * NULL means it has never been load, '' means nothing found
   */
  protected $aSource = null;
  protected $sSourceParameters = null;
  
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
  
  protected function getSource($bText = true, $bDeclaration = false) {
    
    $aResult = array();
    
    if ($this->aSource === null) {
      
      if (!$aSource = $this->getClass()->getSource()) {
        
        $this->throwException('Cannot load source code for method');
      }
      
      $iStart = $this->getReflector()->getStartLine() - $this->getClass()->getOffset() - 1;
      $iEnd = $this->getReflector()->getEndLine() - $this->getClass()->getOffset();
      
      $this->aSource = array_slice($this->getClass()->getSource(), $iStart, $iEnd - $iStart);
    }
    
    if ($this->aSource) {
      
      if (!$bDeclaration) $aResult = array_slice($this->aSource, 1, -1);
      else $aResult = $this->aSource;
    }
    
    if ($bText) return implode('', $aResult);
    else return $aResult;
  }
  
  protected function getSourceParameters() {
    
    if (!$aSource = $this->getParent()->getSource()) {
      
      $this->throwException('Cannot load source code for parameters');
    }
    
    preg_match('\((.+)\) {/', $aSource[0], $aResult);
    $this->sSource = $aResult[0];
  }
  
  public function throwException($sMessage, $mSender = array()) {
    
    $mSender = (array) $mSender;
    $mSender[] = '@method ' . $this->getName();
    
    return parent::throwException($sMessage, $mSender);
  }
  
  public function parse() {
    
    $result = new XML_Element('method', null, array(
      'name' => $this->getName()), $this->getControler()->getNamespace());
    
    $result->addNode('source', $this->getSource());
    $result->addNode('comments', $this->getReflector()->getDocComment());
    
    return $result;
  }
  
  public function __toString() {
    
    return
      '  ' . implode(' ', Reflection::getModifierNames($this->getReflector()->getModifiers())) .
      ' function ' . $this->getName() .
      '(' . implode(', ', $this->aParameters) . ") {\n" .
      $this->getSource() .
      "  }";
  }
}

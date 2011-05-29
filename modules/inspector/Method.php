<?php

class InspectorMethod extends InspectorReflector implements InspectorReflectorInterface {
  
  protected $parent;
  protected $aParameters = array();
  
  /**
   * NULL means it has never been loaded, ''/array() means nothing found
   */
  protected $aSource = null;
  protected $sSourceParameters = null;
  
  public function __construct(ReflectionMethod $reflector, InspectorReflectorInterface $parent) {
    
    $this->parent = $parent;
    $this->reflector = $reflector;
    
    $this->loadParameters();
  }
  
  public function getReflector() {
    
    return parent::getReflector();
  }
  
  protected function loadParameters() {
    
    foreach ($this->getReflector()->getParameters() as $parameter) {
      
      $this->aParameters[] = $this->getControler()->create('parameter', array($parameter, $this));
    }
  }
  
  protected function getControler() {
    
    return $this->getParent()->getControler();
  }
  
  protected function getSource($bText = true, $bDeclaration = false) {
    
    $aResult = array();
    
    if ($this->aSource === null) {
      
    	$this->aSource = array();
    	
      if (!$aSource = $this->getParent()->getSource()) {
        
        $this->throwException('Cannot load source code for method');
      }
      
      $iStart = $this->getReflector()->getStartLine() - $this->getParent()->getOffset() - 1;
      $iEnd = $this->getReflector()->getEndLine() - $this->getParent()->getOffset();
      
      $this->aSource = array_slice($this->getParent()->getSource(), $iStart, $iEnd - $iStart);
    }
    
    if ($this->aSource) {
      
      if (!$bDeclaration) $aResult = array_slice($this->aSource, 1, -1);
      else $aResult = $this->aSource;
    }
    
    if ($bText) return implode('', $aResult);
    else return $aResult;
  }
  
  public function getSourceParameters() {
    
  	if ($this->sSourceParameters === null) {
  		
  		$this->sSourceParameters = '';
  		
	    if (!$aSource = $this->getSource(false, true)) {
	      
	      $this->throwException('Cannot load source code for parameters');
	    }
	    
	    preg_match('/\((.+)\) {/', $aSource[0], $aResult);
	    $this->sSourceParameters = $aResult[0];
  	}
    
    return $this->sSourceParameters;
  }
  
  public function throwException($sMessage, $mSender = array()) {
    
    $mSender = (array) $mSender;
    $mSender[] = '@method ' . $this->getName();
    
    return parent::throwException($sMessage, $mSender);
  }
  
  public function parse() {
    
    $node = Arguments::buildDocument(array(
      'method' => array(
        '@name' => $this->getName(),
        'modifiers' => $this->getReflector()->getModifiers(),
        'comments' => $this->getReflector()->getDocComment(),
        $this->aParameters,
        'source' => $this->getSource(),
      ),
    ), $this->getControler()->getNamespace());
    
    if ($this->getReflector()->getDeclaringClass() != $this->getParent()->getReflector()) {
      
      $node->setAttribute('class', $this->getReflector()->getDeclaringClass()->getName());
    }
    
    return $node; 
  }
  
  public function __toString() {
      
    if ($sComment = $this->getReflector()->getDocComment()) {
      
      $sComment = "  " . $sComment . "\n";
    }
    
    return
      $sComment .
      '  ' . implode(' ', Reflection::getModifierNames($this->getReflector()->getModifiers())) .
      ' function ' . $this->getName() .
      '(' . implode(', ', $this->aParameters) . ") {\n" .
      $this->getSource() .
      "  }";
  }
}

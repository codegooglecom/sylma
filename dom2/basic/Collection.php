<?php

namespace sylma\dom\basic;
use \sylma\dom;

class Collection implements \Iterator {
  
  private $aNodes = array();
  private $aStore = array();
  private $iIndex = 0;
  
  public $length = 0;
  
  public function __construct($mValues = null) {
    
    if ($mValues) $this->add($mValues);
  }
  
  public function getFirst() {
    
    return $this->item(0);
  }
  
  public function item($iKey) {
    
    if (array_key_exists($iKey, $this->aNodes)) return $this->aNodes[$iKey];
    else return null;
  }
  
  public function __call($sMethod, $aArguments) {
    
    $method = null;
    
    foreach ($this->aNodes as $oNode) {
      
      if (!$method) $method = new ReflectionMethod($node, $sMethod);
      $method->invokeArgs($node, $aArguments);
    }
  }
  
  public function rewind() {
    
    $this->iIndex = 0;
  }
  
  public function next() {
    
    $this->iIndex++;
  }
  
  public function key() {
    
    return $this->iIndex;
  }
  
  public function current() {
    
    if (array_key_exists($this->iIndex, $this->aNodes)) return $this->aNodes[$this->iIndex];
    else return null;
  }
  
  public function valid() {
    
    return ($this->iIndex < count($this->aNodes));
  }
  
  public function addNode($mValue) {
    
    $this->aNodes[] = $mValue;
    $this->length++;
  }
  
  public function addArray($aValue) {
    
    foreach ($aValue as $oNode) $this->addNode($oNode);
  }
  
  public function store() {
    
    $this->aStore[] = $this->iIndex;
  }
  
  public function restore() {
    
    $this->iIndex = array_pop($this->aStore);
  }
  
  public function reverse() {
    
    $this->aNodes = array_reverse($this->aNodes);
    $this->rewind();
  }
  
  public function implode($sSeparator = ' ') {
    
    $aResult = array();
    
    foreach ($this->aNodes as $oNode) {
      
      $aResult[] = $oNode; 
      $aResult[] = $sSeparator;
    }
    
    array_pop($aResult);
    return $aResult;
  }
  
  public function __toString() {
    
    return implode('', $this->implode());
  }
}


<?php

class XML_Attribute extends DOMAttr implements NodeInterface {
  
  public function __construct($sName, $sValue) {
    
    $sValue = checkEncoding($sValue);
    
    parent::__construct($sName, $sValue);
  }
  
  public function isElement() {
    
    return false;
  }
  
  public function isText() {
    
    return false;
  }
  
  public function getPrevious() {
    
    return null;
  }
  
  public function getNext() {
    
    return null;
  }
  
  public function getPrefix() {
    
    return $this->prefix;
  }
  
  public function getName($bFull = false) {
    
    if ($bFull && $this->getPrefix()) return $this->getPrefix().':'.$this->name;
    else return $this->name;
  }
  
  public function read() {
    
    return $this->getValue();
  }
  
  public function getValue() {
    
    return $this->value;
  }
  
  public function getPath() {
    
    return $this->getParent()->getPath() . ' @attribute ' . $this->getName();
  }
  
  public function getParent() {
    
    return $this->ownerElement;
  }
  
  public function useNamespace($sNamespace) {
    
    return $this->getNamespace() == $sNamespace;
  }
  
  public function getNamespace() {
    
    return $this->namespaceURI;
  }
  
  public function getDocument() {
    
    return $this->ownerDocument;
  }
  
  public function remove() {
    
    $this->ownerElement->removeAttributeNode($this);
  }
  
  public function set($sValue) {
    
    $this->value = (string) checkEncoding($sValue);
  }
  
  public function __toString() {
    
    // if ($this->getNamespace()) $sPrefix = $this->getNamespace() . ':';
    // else $sPrefix = '';
    
    return $this->getName(true).'="'.xmlize($this->value).'"';
  }
}


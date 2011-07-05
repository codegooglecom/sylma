<?php

class XML_CData extends DOMCdataSection implements NodeInterface {
  
  public function setValue($mValue) {
    
    $this->data = (string) $mValue;
  }
  
  public function getValue() {
    
    return $this->data;
  }
  
  public function remove() {
    
    return $this->parentNode->removeChild($this);
  }
  
  public function isText() {
    
    return false;
  }
  
  public function getDocument() {
    
    return $this->ownerDocument;
  }
  
  public function getParent() {
    
    return $this->parentNode;
  }
  
  public function getNext() {
    
    return $this->nextSibling;
  }
  
  public function getPrevious() {
    
    return $this->previousSibling;
  }
  
  public function isElement() {
    
    return false;
  }
  
  public function getPath() {
    
    return $this->getParent()->getPath() . ' @dom/cdata';
  }
  
  public function __toString() {
    
    return $this->data;
    //return "<![CDATA[\n".$this->data."]]>\n";
  }
}


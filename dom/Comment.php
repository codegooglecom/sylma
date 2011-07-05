<?php

class XML_Comment extends DOMComment implements NodeInterface {
  
  // private $aRights = array();
  
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
  
  public function remove() {
    
    return $this->parentNode->removeChild($this);
  }
  /*
  public function formatOutput($iLevel = 0) {
    
    return null;
  }*/
  
  public function isText() {
    
    return false;
  }
  
  public function isElement() {
    
    return false;
  }
  
  public function getPath() {
    
    return $this->getParent()->getPath() . ' @dom/comment';
  }
  
  public function __toString() {
    
    return "<!--{$this->data}-->";
  }
}


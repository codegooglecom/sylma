<?php

class XML_Text extends DOMText implements NodeInterface {
  
  // private $aRights = array();
  const COMPARE_BAD_ELEMENT = 1;
  
  public function __construct($mContent) {
    
    if (is_object($mContent)) {
      
      if (method_exists($mContent, '__toString')) $mContent = (string) $mContent;
      else {
        
        Controler::addMessage(xt('Object " %s " cannot be converted to string !', new HTML_Strong(get_class($mContent))), 'xml/error');
        $mContent = '';
      }
    }
    
    $mContent = checkEncoding($mContent);
    
    // if (!(is_string($mContent) || is_numeric($mContent))) $mContent = '';
    // if ($mContent === 0) $mContent = '00'; //dom bug ?
    
    parent::__construct($mContent);
  }
  
  public function getDocument() {
    
    return $this->ownerDocument;
  }
  
  public function getNext() {
    
    return $this->nextSibling;
  }
  
  public function getPrevious() {
    
    return $this->previousSibling;
  }
  
  public function getParent() {
    
    return $this->parentNode;
  }
  
  public function replace($mChild) {
    
    if (is_string($mChild)) $oChild = new XML_Text($mChild);
    else $oChild = $mChild;
    
    $this->insertBefore($oChild);
    $this->remove();
    return $oChild;
  }
  
  public function compare(NodeInterface $element) {
    
    if ($element->isText() && $element->getValue() == $this->getValue()) return 0;
    
    return self::COMPARE_BAD_ELEMENT;
  }
  
  public function remove() {
    
    return $this->parentNode->removeChild($this);
  }
  /*
  public function formatOutput($iLevel = 0) {
    
    return null;
  }*/
  
  public function isText() {
    
    return true;
  }
  
  public function isElement() {
    
    return false;
  }
  
  public function getValue() {
    
    return $this->nodeValue;
  }
  
  public function getPath() {
    
    return $this->getParent()->getPath() . ' @dom/text';
  }
  
  public function __toString() {
    
    try {
      
      return xmlize($this->nodeValue);
      
		} catch ( Exception $e ) {
      
			dspm('Text : '.$e->getMessage(), 'xml/error');
		}
  }
}


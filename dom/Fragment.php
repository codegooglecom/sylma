<?php

class XML_Fragment extends DOMDocumentFragment {
  
  private $sNamespace;
  
  // public function __construct($sNamespace = null) {
    
    // $this->sNamespace = $sNamespace;
    // parent::__construct();
    
    // $doc = new XML_Document;
    // $doc->importNode($this);
    // dspf(get_object_vars($this));
  // }
  
  public function setNamespace($sNamespace) {
    
    $this->sNamespace = $sNamespace;
  }
  
  public function getNamespace() {
    
    return $this->sNamespace;
  }
  
  public function getFirst() {
    
    return $this->firstChild;
  }
  
  public function add() {
    
    $result = null;
    
    if (count(func_get_args()) > 1) {
      
      $result = $this->add(func_get_args());
    }
    else {
      
      $val = func_get_arg(0);
      
      if (is_array($val)) {
        
        $result = array();
        foreach ($val as $arg) $result[] = $this->add($arg);
      }
      else {
        
        $result = $this->insertChild($val);
      }
    }
    
    return $result;
  }
  
  public function insertChild(DOMNode $child) {
    
    if ($child instanceof DOMNode) {
      
      if ($child->getDocument() != $this->getDocument()) $child = $this->getDocument()->importNode($child);
      return parent::appendChild($child);
    }
  }
  
  public function getDocument() {
    
    // return new XML_Document;
    return $this->ownerDocument;
  }
  
  public function addNode($sName, $content = null, $aAttributes = array(), $sNamespace = null) {
    // dspf(get_object_vars($this));
    if (!$this->getDocument()) Sylma::throwException('tmp no doc');
    
    if ($sNamespace === null) $sNamespace = $this->getNamespace();
    
    $node = $this->getDocument()->createNode($sName, $content, $aAttributes, $sNamespace);
    $this->appendChild($node);
    
    return $node;
  }
}


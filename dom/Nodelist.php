<?php

class XML_NodeList implements Iterator {
  
  private $aNodes = array();
  public $length = 0;
  protected $iIndex = 0;
  private $aStore = array();
  
  public function __construct($mValues = null) {
    
    if ($mValues) $this->add($mValues);
  }
  
  public function toArray($sMode = null) {
    
    $aResults = array();
    
    foreach ($this as $oNode) {
      
      switch ($sMode) {
        
        case 'id' : $aResults[$oNode->getAttribute('id')] = $oNode->getChildren()->toArray(); break;
        case 'name' : $aResults[] = $oNode->getName(); break;
        // case 'attribute' : $aResult[] = $oNode->getAttribute($sAttribute);
        case null :
          
          // if ($oNode->isEmpty()) $aResults[] = $oNode->getName();
          if ($oNode->isText()) $aResults[] = (string) $oNode;
          else $aResults[$oNode->getName()] = $oNode->getValue();
          
        break;
        
        default : $aResults[$oNode->getAttribute($sMode)] = $oNode->read();
      }
    }
    
    return $aResults;
  }
  
  public function getFirst() {
    
    return $this->item(0);
  }
  
  public function item($iKey) {
    
    if (array_key_exists($iKey, $this->aNodes)) return $this->aNodes[$iKey];
    else return null;
  }
  
  public function __call($sMethod, $aArguments) {
    
    foreach ($this->aNodes as $oNode) {
      
      if (method_exists($oNode, $sMethod)) {
        
        $aEvalArguments = array();
        for ($i = 0; $i < count($aArguments); $i++) $aEvalArguments[] = "\$aArguments[$i]";
        
        eval('$oResult = $oNode->$sMethod('.implode(', ', $aEvalArguments).');');
        
      } else Controler::addMessage(xt('NodeList : Méthode %s introuvable', new HTML_Strong($sMethod)), 'xml/error');
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
  
  public function view() {
    
    $aResult = array();
    foreach ($this->aNodes as $oNode) $aResult[] = $oNode->view(true, true, false);
    
    return new HTML_Div($aResult);
  }
  
  public function valid() {
    
    return ($this->iIndex < count($this->aNodes));
  }
  
  public function addNode($mValue) {
    
    $this->aNodes[] = $mValue;
    $this->length++;
  }
  
  public function add($mValue) {
    
    if ($mValue) {
      
      if (is_array($mValue) || // TODO, bad test
        (is_object($mValue) && ($mValue instanceof DOMNodeList || $mValue instanceof DOMNamedNodeMap || $mValue instanceof Iterator))) {
        
        foreach ($mValue as $oNode) $this->addNode($oNode);
        
      } else $this->addNode($mValue);
    }
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
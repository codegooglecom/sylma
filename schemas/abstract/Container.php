<?php

require_once('Basic.php');

abstract class XSD_Container extends XSD_Basic { // Used by XSD_Node, XSD_Group, XSD_Type
  
  private $sName = '';
  private $bNew = false; // anonymous definition
  
  protected $oParticle = null;
  
  public function __construct(XML_Element $oSource, $oParent, $oNode = null, XSD_Parser $oParser = null) {
    
    parent::__construct($oSource, $oParent, $oNode, $oParser);
    
    if (!$this->sName = $oSource->getAttribute('name')) {
      
      $this->bNew = true;
      $this->sName = $this->sPath = str_replace('/', '-', $oParent->getPath());
    }
  }
  
  protected function isNew() { // Classes : type, group
    
    return $this->bNew;
  }
  
  public function getName() {
    
    return $this->sName;
  }
  
  public function getParticle() {
    
    return $this->oParticle;
  }
  
  public function setParticle(XSD_Basic $oParticle) {
    
    return $this->oParticle = $oParticle;
  }
  
  public function getPath() { // Classes : [type], [group]
    
    if (!$this->sPath) $this->sPath = ($this->getParent() ? $this->getParent()->getPath().'/' : '').$this->getName();
    
    return $this->sPath;
  }
  
  public function getElement(XML_Element $oElement) { // Instances : model, [particle], group
    
    return $this->getParticle()->getElement($oElement);
  }
}


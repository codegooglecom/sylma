<?php

require_once('abstract/Instance.php');

class XSD_Model extends XSD_Instance { // XSD_ElementInstance
  
  private $oParticle = null;
  private $oNode = null;
  private $bValidName = false; // define if node name correspond to element (class) name
  
  public function __construct(XSD_Element $oClass, XML_Element $oNode = null, XSD_Instance $oParent = null) {
    
    parent::__construct($oClass, $oParent);
    
    $this->oNode = $oNode;
    
    if ($oNode) {
      
      if ($oNode->getName() === $oClass->getName()) $this->bValidName = true;
      
      if ($oNode->isComplex()) {
        
        // complexType
        $this->buildParticle();
        
        if ($oNode->hasChildren()) $this->buildChildren();
        if ($oNode->hasAttributes()) $this->buildAttributes();
      }
      else if ($oClass->getSource()->getAttribute('file', $this->getNamespace())) { // look for lc:file
        
        $this->getParser()->addFile($oNode, $oClass->getSource(), $this);
      }
      
    } else $this->setStatut('missing');
  }
  
  public function buildParticle() {
    
    if (!$this->getClass()) $this->dspm(xt('Aucun élément classe défini pour %s', view($this)), 'xml/error');
    else {
      
      if ($this->getClass()->getType()->isComplex()) { // complex type
        
        if (!$this->getClass()->getType()->getParticle()) { // node is complex but type is simple
          
          $this->getParser()->dspm(xt('Impossible de construire l\'élément %s, particule manquante', view($this->getNode())), 'xml/warning');
          
        } else {
          
          if (!$this->getClass()->getType()->isMixed()) // complex not mixed
            $this->oParticle = $this->getClass()->getType()->getParticle()->getInstance($this);
        }
        
      } else { // simple type
        
        if ($this->getNode()) { // node is mixed but type is simple
          
          if ($this->useMessages()) $this->addMessage(
            xt('L\'élément %s ne devrait pas contenir d\'autre éléments, le type %s est attendu',
            view($this->getNode()), view($this->getClass()->getSource())), 'content', 'badtype');
          
          $this->isValid(false);
        }
      } 
    }
  }
  
  public function getParticle() {
    
    return $this->oParticle;
  }
  
  public function getNode() {
    
    return $this->oNode;
  }
  
  public function getValue() {
    
    return $this->getNode()->read();
  }
  
  public function getParser() {
    
    return $this->getClass()->getParser();
  }
  
  public function getModel() {
    
    return $this;
  }
  
  public function isValidName() {
    
    return $this->bValidName;
  }
  
  private function buildChildren() {
    
    if ($this->getParticle()) {
      
      foreach ($this->getNode()->getChildren() as $oChild) {
        
        if (($oCurrent = $this->getClass()->getType()->getElement($oChild))) {
          
          $this->getParticle()->add($oChild, $oCurrent->getParents());
          
        } else {
          
          if ($this->useMessages()) $this->addMessage(
            xt('L\'élément %s n\'est pas autorisé au sein de l\'élément %s',
            view($oChild->getName()), view($this->getClass()->getName())), 'element', 'denied');
          
          $this->isValid(false);
          
          // $this->getParticle()->add(); TODO
          if (!$this->keepValidate()) break;
        }
      }
    }
  }
  
  private function buildAttributes() {
    
    // TODO
  }
  
  public function parse() {
    
    $iID = $this->getParser()->getID();
    
    $oModel = new XML_Element('model', null, array(
      'name' => $this->getClass()->getName(),
      'id' => $iID,
      'element' => $this->getClass()->getID(),
      'statut' => $this->getStatut()), $this->getNamespace());
    
    if ($this->getNode()) {
      
      // copy @lc:* to current node
      if ($this->getParser()->useMark()) {
        
        $this->getNode()->cloneAttributes($this->getClass()->getSource(), null, $this->getNamespace());
      }
      
      $this->getNode()->setAttribute('lc:model', $iID, $this->getNamespace());
      
      if ($this->getParticle() || $this->getNode()->isComplex()) { // complex type or complex node
        
        //$oModel->cloneAttributes($this->getClass()->getSource(), array('minOccurs', 'maxOccurs'));
        
        $oContent = $oModel->addNode('schema', null, null, $this->getNamespace());
        $oContent->add($this->getParticle());
        
        $oModel->setAttribute('base', $this->getClass()->getType());
        
      } else if ($this->getClass()->getType()->hasRestrictions()) { // simple type with restrictions
        
        $oModel->setAttribute('base', $this->getClass()->getType());
        
      } else { // base type
        
        // $oModel->setAttribute('type', $this->getClass()->getType());
      }
      
      if ($this->getMessages()) $oModel->shift(new XML_Element('annotations', $this->getMessages(), null, $this->getNamespace()));
    }
    
    return $oModel;
  }
}


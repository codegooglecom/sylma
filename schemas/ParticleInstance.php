<?php

require_once('abstract/Instance.php');

class XSD_ParticleInstance extends XSD_Instance {
  
  private $aChildren = array();
  
  private $iSeek = 0; // current validated child position
  private $aParticles = array(); // child instance particles
  private $aElements = array(); // child instance elements
  
  public function shiftSeek() {
    
    $this->iSeek++;
  }
  
  public function getSeek() {
    
    return $this->iSeek;
  }
  
  public function insert(XSD_Instance $oInstance) {
    
    $iIndex = $this->getSeek();
    $aChildren = $this->getChildren();
    
    if ($iIndex === null || $iIndex == count($aChildren)) {
      
      // insert at last
      $this->aChildren[] = $oInstance;
    }
    else if (!$iIndex) {
      
      // insert at first
      array_unshift($this->aChildren, $oInstance);
    }
    else {
      
      // insert elsewhere
      $this->aChildren = array_merge(
        array_slice($aChildren, 0, $iIndex),
        array($oInstance),
        array_slice($aChildren, $iIndex));
    }
    
    $this->shiftSeek();
  }
  
  public function add(XML_Element $oElement, array $aParents) {
    
    if ($aParents) {  // browse inside particles
      
      $oParent = array_pop($aParents);
      
      $oResult = null;
      
      if ($this->aParticles) { // first, search in ever added particle
        
        foreach ($this->aParticles as $oParticle) {
          
          if ($oParticle->getClass() === $oParent) {
            
            $oResult = $oParticle;
            break;
          }
        }
      }
      
      if (!$oResult) { // nothing ? look in type particle
        
        foreach ($this->getClass()->getParticles() as $oParticle) {
          
          if ($oParticle === $oParent) {
            
            $oResult = $oParticle;
            break;
          }
        }
        
        if ($oResult) { // build new particle
          
          $oResult = $oResult->getInstance($this);
          
          $this->aParticles[] = $oResult;
          $this->aChildren[] = $oResult;
        }
      }
      
      if (!$oResult) $this->dspm(xt('Erreur, particule %s introuvable dans le type', view($oParent)), 'xml/warning'); // shouldn't happend
      else $oResult->add($oElement, $aParents);
      
    } else { // this one
      
      $this->aChildren[] = $this->getClass()->getElement($oElement)->getInstance($this, $oElement);
    }
  }
  
  public function getChildren() {
    
    return $this->aChildren;
  }
  
  public function parse() {
    
    $oResult = new XML_Element($this->getClass()->getSource()->getName(), null, array(
        'statut' => $this->getStatut()), $this->getNamespace());
    
    if ($this->getParent() instanceof XSD_ParticleInstance) { // is not root particle
      
      // required to parse children before self
      $oSchema = $oResult->addNode('schema', $this->getChildren(), null, $this->getNamespace()); 
      
      if ($this->getMessages()) { // if messages, add 2 children
        
        $oResult->insertNode('annotations', $this->getMessages(), null, $this->getNamespace(), $oSchema);
        
      } else {
        
        $oResult->add($oSchema->getChildren());
        $oSchema->remove();
      }
      
    } else { // if first, do not display itself
      
      // required to parse children before self
      $oResult = $oResult->add($this->getChildren());
      $this->getParent()->addMessages($this->getMessages());
    }
    
    return $oResult;
  }
  
}


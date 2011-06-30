<?php

require_once('abstract\Class.php');
require_once('ParticleInstance.php');

class XSD_Particle extends XSD_Class {
  
  private $aChildren = array();
  
  private $aElements = array();
  private $aParticles = array();
  
  public function __construct(XML_Element $oSource, $oParent, $aPath = array()) {
    
    parent::__construct($oSource, $oParent);
    $this->indexChildren($aPath);
  }
  
  public function getParticles() {
    
    return $this->aParticles;
  }
  
  public function getChildren() {
    
    return $this->aChildren;
  }
  
  public function indexChildren($aPath) {
    
    if ($aPath) $sPath = $aPath[0];
    else $sPath = '';
    
    $aResult = array();
    
    foreach ($this->getSource()->getChildren(null, null, true) as $oComponent) {
      
      $oResult = null;
      
      switch ($oComponent->getName()) {
        
        case 'group' :
          
          $oResult = new XSD_GroupReference($oComponent, $this);
          $this->aParticles[] = $oResult;
          
        break;
        
        case 'choice' :
        case 'sequence' :
          
          $oResult = new XSD_Particle($oComponent, $this, $aPath);
          $this->aParticles[] = $oResult;
          
        break;
        
        case 'element' :
          
          $sName = $oComponent->hasAttribute('name') ? $oComponent->getAttribute('name') : $oComponent->getAttribute('ref');
          
          if (!$sName) {
            
            $this->dspm(xt('Aucun nom ou référence défini pour %s', view($oComponent)), 'xml/error');
            
          } else if (!$sPath || $sName == $sPath) {
            
            $oResult = $this->getParser()->create('element', array($oComponent, $this, null, null, $aPath));
            $this->aElements[$sName] = $oResult;
          }
          
        break;
      }
      
      if ($oResult) $this->aChildren[] = $oResult;
    }
  }
  
  public function getElement(XML_Element $oElement) {
    
    $oResult = null;
    $sName = $oElement->getName();
    
    if (array_key_exists($sName, $this->aElements)) $oResult = $this->aElements[$sName];
    else {
      
      foreach ($this->aParticles as $oParticle) {
        if ($oResult = $oParticle->getElement($oElement)) break; 
      }
    }
    
    return $oResult;
  }
  
  /**
 * Validate self and distribute, on a name base, xs:element to instances
   * Most part of the model building will append here
   * @param XSD_Instance The instance to validate to
   * @param array $aPath The list of parent's name if validation append on an inside node
   * @param boolean $bMessages Do must the validation display error message.
   *  If validation failed before, error messages will not be displayed for only builded model
   * @return boolean Return [true] if validation success
   */
  public function validate(XSD_Instance $oInstance, $aPath = array(), $bMessages = true) {
    
    if (!$oInstance) { // temp ?
      
      $this->dspm(xt('Aucun instance reçue pour valider l\'élément %s', view($this->getSource())), 'xml/error');
      return false;
    }
    
    $bResult = false;
    $oPrevious = null;
    
    $iShift = 0;
    
    $aSubInstances = $oInstance->getChildren();
    $aChildren = $this->getChildren();
    
    list(,$oSubInstance) = each($aSubInstances);
    list(,$oChild) = each($aChildren);
    
    // TODO, if sequence
    
    while (($this->keepValidate() || $this->getParser()->isValid()) && $oChild) {
      
      if ($oSubInstance && $oChild->hasInstance($oSubInstance)) {
        
        $oInstance->shiftSeek();
        
        $bResult = $oChild->validate($oSubInstance, $aPath);
        $this->getParser()->isValid($bResult);
        // || !($oSubInstance->isValidName() && 
          // $oSubInstance->getNode()->getNext() &&
          // $oSubInstance->getNode()->getNext()->getName() == $oSubInstance->getNode()->getName())
        if ($oChild->getMax() <= 1) {
            
            list(,$oChild) = each($aChildren);
        }
        
        $oPrevious = $oSubInstance->getNode();
        list(,$oSubInstance) = each($aSubInstances);
      }
      else {
        
        if ($oChild->getSource()->testAttribute('editable', true, $this->getNamespace())) {
          
          if ($oChild->isRequired()) { // if required validation fails
            
            $bResult = $this->getParser()->isValid(false);
            if (!$this->keepValidate()) break;
          }
          
          if ($this->getParser()->useModel()) {
            
            if (!$oChild->isRequired() && $oChild->getMax() > 1 && !$aPath) {
              
              if ($oNode = $oInstance->getNode()) {
                
                $aAttributes = array('name' => $oChild->getName());
                
                $sPath = $oChild->getName();
                $oParent = null;
                
                // build the path. TODO should use a generic function
                
                do {
                  
                  if ($oParent) $oParent = $oParent->getParent();
                  else $oParent = $oNode;
                  
                  $sPath = $oParent->getName().'/'.$sPath;
                  
                } while (!$oParent->isRoot());
                
                if ($oPrevious) $oPrevious = $oNode->insertNode(
                  'lc:link-add', $sPath, $aAttributes, $this->getNamespace(), $oPrevious, true);
                else $oPrevious = $oNode->addNode('lc:link-add', $sPath, $aAttributes, $this->getNamespace());
              }
            }
            else { // else is required or optional but unique
              
              $oNewInstance = $oChild->buildInstance($oInstance, $oPrevious);
              $bSubResult = $oChild->validate($oNewInstance, $aPath, false);
              
              if (!$oChild->isRequired()) $oNewInstance->setStatut('optional');
              else {
                
                $bResult = false;
                $oNewInstance->isValid(false);
                
                if (!$aPath && $oNewInstance && $bMessages && $oInstance->useMessages()) { // set message and statut
                  
                  $oNewInstance->setStatut('missing');
                  $oNewInstance->addMessage(xt('Ce champ doit être indiqué'), 'content', 'invalid');
                }
              }
              
              $oPrevious = $oNewInstance->getNode();
            }
          }
        }
        
        list(,$oChild) = each($aChildren);
      }
    }
    
    return $bResult;
  }
  
  public function buildInstance(XSD_Instance $oParent) {
    
    $oParent->insert($this->getInstance($oParent));
  }
  
  public function getInstance($oParent) {
    
    $oInstance = new XSD_ParticleInstance($this, $oParent);
    $this->aInstances[] = $oInstance; // used for final schema validation
    
    return $oInstance;
  }
  
  public function parse() {
    
    $oParticle = new XML_Element($this->getSource()->getName(), $this->aChildren, null, $this->getNamespace());
    
    return $oParticle;
  }
}


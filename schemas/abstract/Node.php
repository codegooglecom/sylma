<?php

require_once('Container.php');

abstract class XSD_Node extends XSD_Container { // Used by XSD_Element, XSD_Attribute
  
  private $oType = null;
  private $sID = '';
  
  protected $aInstances = array(); // instanced particles derived from this particle
  
  abstract public function buildInstance(XSD_Instance $oParent);
  
  /**
   * @param XML_Element $oSource Node that represents this object in the schema (xs:element, xs:attribute)
   * @param XSD_Particle|null $oParent Particle that contains this node
   * @param $oNode Unusefull for this class, due to extends
   * @param XSD_Parser $oParser Main parser, only necessary for root XSD_Node
   * @param array $aPath Array of element's name, parents of targeted element
   */
  
  public function __construct(XML_Element $oSource, $oParent, $oNode = null, XSD_Parser $oParser = null, $aPath = array()) {
    
    parent::__construct($oSource, $oParent, $oNode, $oParser);
    
    // if parent sequence is not direct child of element, build parent name and get it
    if ($this->getParent() && $this->getParent()->getParent() instanceof XSD_Particle) $this->sID = $this->getParser()->getID();
    
    $sType = $oSource->getAttribute('type');
    
    if ($oSource->hasAttribute('minOccurs')) $this->mMin = $oSource->getAttribute('minOccurs');
    if ($oSource->hasAttribute('maxOccurs')) $this->mMax = $oSource->getAttribute('maxOccurs');
    
    if ($oSource->getAttribute('key-ref', $this->getNamespace())) {
      
      $oSource->setAttribute('lc:full-name', $this->getName()); // TODO : complete name from root
      $this->getParser()->addRef($oSource);
    }
    
    if ($oSource->hasChildren()) {
      
      if ($sType) $this->dspm(xt('Attribut %s interdit dans %s mais le processus continue',
        new HTML_Strong('type'), view($oSource)), 'xml/error');
      
      if ($sRef = $oSource->getAttribute('ref')) {
        
        // TODO ref
        
      } else {
        
        if (!$oFirst = $oSource->getFirst()) $this->dspm(xt('Type indéfini pour le composant %s', view($oSource)), 'xml/error');
        else {
          
          $this->oType = $this->getParser()->create('complextype', array($oFirst, $this, null, null, $aPath));
          $this->getParser()->addType($this->getType(), $oFirst); // WARNING : maybe bad $oFirst, may be the referencer
        }
      }
      
    } else {
      
      if ($sType) $this->oType = $this->getParser()->getType($sType, $oSource, $aPath);
    }
  }
  
  public function getID() {
    
    return $this->sID;
  }
  
  public function isRequired() {
    
    return intval($this->getMin()) >= 1;
  }
  
  public function getInstances() {
    
    return $this->aInstances;
  }
  
  protected function addInstance(XSD_Instance $oInstance) {
    
    $this->aInstances[] = $oInstance;
    
    return $oInstance;
  }
  
  public function hasInstance(XSD_Instance $oNeedle) { // TODO : replicate of XSD_Class, DEBUG protected
    
    foreach ($this->aInstances as $oInstance) if ($oNeedle === $oInstance) return true;
    
    return false;
  }
  
  public function getType() {
    
    return $this->oType;
  }
  
}


<?php

require_once('abstract\Node.php');

class XSD_Element extends XSD_Node {
  
  public function __construct(XML_Element $oSource, $oParent, $oNode = null, XSD_Parser $oParser = null, $aPath = array()) {
    
    array_shift($aPath);
    
    parent::__construct($oSource, $oParent, $oNode, $oParser, $aPath);
    if (count($aPath) == 1) $this->getParser()->setElement($this);
  }
  
  public function getParents() {
    
    return $this->getParent()->getClasses();
  }
  
  public function validate(XSD_Instance $oInstance, $aPath = array(), $bMessages = true) {
    
    array_shift($aPath);
    return $this->getType()->validate($oInstance, $aPath, $bMessages);
  }
  
  /**
   * @param XSD_Instance $oParent Parent particle instance to set instance to
   * @param XML_Element|null $oPrevious Previous element to indicate position to new node, last if null
   * @return XSD_Instance The builded instance object
   * Create new node and instance with getInstance() in node of $oParent's model from his own type
   */
  
  public function buildInstance(XSD_Instance $oParent, XML_Element $oPrevious = null) {
    
    $oInstance = null;
    
    if ($this->getType()) {
      
      $oElement = $oParent->getModel()->getNode()->insertChild(
        new XML_Element($this->getName(), null, null, $this->getType()->getNamespace(true)),
        $oPrevious, true);
      
      if ($sDefault = $this->getSource()->getAttribute('default')) {
        
        $oElement->set($sDefault);
        
      } else if ($sDefault = $this->getSource()->getAttribute('default-query', $this->getNamespace())) {
        
        if (!Sylma::get('db/enable'))
          $this->dspm('Impossible de déterminer la valeur par défaut. XQuery est nécessaire', 'xml/warning');
        else $oElement->set(Controler::getDatabase()->query($sDefault));
      }
      
      $oInstance = $this->getInstance($oParent, $oElement);
      $oParent->insert($oInstance);
    }
    
    return $oInstance;
  }
  
  /**
   * @param XSD_Particle|null $oParent Parent particle instance of the new element instance
   * @param XML_Element $oNode Source node of the instance
   * Create then add from $oNode, a new XSD_Model in instances array
   */
  
  public function getInstance($oParent, XML_Element $oNode) {
    
    $oModel = $this->getParser()->create('model', array($this, $oNode, $oParent));
    if (!$this->isRequired()) $oModel->setStatut('optional');
    
    return $this->addInstance($oModel);
  }
  
  public function parse() {
    
    $oResult = new XML_Element('element', null, array(
      'name'=> $this->getName(),
      'id' => $this->getID()), $this->getNamespace());
    
    if ($this->getType()->isBasic()) $oResult->setAttribute('basic-type', $this->getType());
    else $oResult->setAttribute('type', $this->getType());
    
    $oResult->cloneAttributes($this->getSource(), array('minOccurs', 'maxOccurs'));
    
    // copy @lc:* to current node
    
    $oResult->cloneAttributes($this->getSource(), null, $this->getNamespace());
    
    return $oResult;
  }
}


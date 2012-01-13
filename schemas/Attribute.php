<?php

require_once('abstract/Node.php');

class XSD_Attribute extends XSD_Node {
  
  public function buildInstance(XSD_Instance $oParent) {
    
    $sName = $this->getName();
    $sNamespace = $this->getNamespace($oParent);
    
    // if ($this->getType()) { // TODO : attribute content validation
    
    if (strpos($sName, ':')) $sNamespace = ''; // usefull for xml:id
    
    if (!$oParent->getNode()->setAttribute($sName, ' ', $sNamespace)) {
      
      $this->dspm(xt('Impossible d\'ajouter l\'attribut %s [%s] dans %s',
        new HTML_Strong($sName), $sNamespace, view($oParent->getNode())), 'xml/error');
      
    } else {
      
      $oAttribute = $oParent->getNode()->getAttributeNode($sName, $sNamespace);
      
      if ($sDefault = $this->getSource()->getAttribute('default')) {
        
        $oAttribute->set($sDefault);
        
      } else if ($sDefault = $this->getSource()->getAttribute('default-query', $this->getNamespace())) {
        
        if (!Sylma::read('db/enable'))
          $this->dspm('Impossible de déterminer la valeur par défaut. XQuery est nécessaire', 'xml/warning');
        else $oAttribute->set(Controler::getDatabase()->query($sDefault));
      }
    }
  }
  
  public function getNamespace($oInstance = null) {
    
    $sNamespace = '';
    
    if (!$oInstance) {
      
      $sNamespace = parent::getNamespace();
    }
    else if (!$oInstance->getNode()->useNamespace($this->getType()->getNamespace())) {
      
      $sNamespace = $this->getType()->getNamespace();
    }
    
    return $sNamespace;
  }
  
  public function validate(XSD_Instance $oInstance, $bMessages = true) {
    
    $bResult = false;
    
    if ($oInstance->getNode()->hasAttribute($this->getName(), $this->getNamespace($oInstance))) $bResult = true;
    else {
      
      if ($this->isRequired()) {
        
        if ($bMessages && $this->useMessages())
          $oInstance->addMessage(xt('Le champ %s doit être indiqué', new HTML_Strong($this->getName())), 'attribute', 'missing');
        
        $oInstance->isValid(false);
      }
      else $bResult = true;
      
      if ($this->getParser()->useModel()) $this->buildInstance($oInstance);
    }
    
    return $bResult;
  }
  
  public function parse() {
    
    $oResult = new XML_Element('attribute', null, array(
      'name' => $this->getName(),
      'basic-type' => $this->getType()), $this->getNamespace());
    
    $oResult->cloneAttributes($this->getSource(), array('minOccurs', 'maxOccurs'));
    
    // copy @lc:* to current node
    $oResult->cloneAttributes($this->getSource(), null, $this->getNamespace());
    
    return $oResult;
  }
}


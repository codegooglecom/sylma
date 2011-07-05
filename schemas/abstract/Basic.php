<?php

abstract class XSD_Basic { // Used by XSD_Container, XSD_Class
  
  protected $sPath = '';
  protected $mMax = 1;
  protected $mMin = 1;
  
  public function __construct(XML_Element $oSource, $oParent, $oNode = null, XSD_Parser $oParser = null) {
    
    $this->oParent = $oParent;
    $this->oParser = $oParser;
    $this->oSource = $oSource;
    $this->oNode = $oNode;
  }
  
  public function getParser() {
    
    return $this->oParser ? $this->oParser : ($this->getParent() ? $this->getParent()->getParser() : null);
  }
  
  public function getParent() {
    
    return $this->oParent;
  }
  
  public function getNamespace() {
    
    return $this->getParser()->getNamespace();
  }
  
  public function useMessages() {
    
    return $this->getParser()->useMessages();
  }
  
  public function keepValidate() {
    
    return $this->getParser()->keepValidate();
  }
  
  public function getSource() {
    
    return $this->oSource;
  }
  
  public function getClasses() { // Extends : element, particle, groupRef, [type]
    
    if ($this->getParent() instanceof XSD_Particle) $aResult[] = $this;
    else $aResult = $this->getParent()->getClasses();
    
    return $aResult;
  }
  
  /**
   * Build the name if anonymous definition
   */
  public function getPath() { // Extends : [container : [type], [group]] / Classes : particle, groupRef
    
    if ($this->getParent()) return $this->getParent()->getPath();
    else $this->dspm(xt('Aucun chemin parent valide pour l\'objet %s %s', view($this), view($this->getSource())), 'xml/error');
  }
  
  public function getMin() {
    
    return $this->mMin;
  }
  
  public function getMax() {
    
    if ($this->mMax == 'unbounded') return 99;
    else return $this->mMax;
  }
  
  protected function dspm($mMessage, $sStatut = SYLMA_MESSAGES_DEFAULT_STAT) {
    
    if ($file = $this->getParser()->getSchema()->getFile()) {
      
      $sFile = $file->parse();
    }
    else {
      
      $sFile = '[unknown]';
    }
    
    $sPath = xt('Schema : %s', $sFile);
    
    return dspm(array($sPath, new HTML_Tag('hr'), $mMessage), $sStatut);
  }
}


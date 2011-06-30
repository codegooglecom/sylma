<?php

class XSD_BaseType {
  
  private $sType = '';
  
  public function __construct($sType, XSD_Parser $oParser) {
    
    $this->sType = $sType;
    $this->oParser = $oParser;
  }
  
  public function getName() {
    
    return substr($this->getType(), 3);
  }
  
  public function getParser() {
    
    return $this->oParser;
  }
  
  public function getType() {
    
    return $this->sType;
  }
  
  public function validate(XSD_Instance $oInstance, $aPath = array(), $bMessages = true) {
    
    $bResult = false;
    
    if ($bMessages) {
      
      $mValue = $oInstance->getValue();
      
      switch ($this->getName()) {
        
        case 'string' : $bResult = is_string($mValue); break; // && !is_numeric($mValue)
        case 'date' : $bResult = preg_match('/^\d{4}-\d{2}-\d{2}$/', $mValue); break;
        case 'dateTime' : break;
        case 'duration' : break;
        case 'boolean' : $bResult = in_array($mValue, array('', '1', '0', 'true', 'false')); break;
        case 'integer' : $bResult = is_integer($mValue) || ctype_digit($mValue); break;
        case 'decimal' : $bResult = is_numeric($mValue) && !is_integer($mValue) && !ctype_digit($mValue); break;
        case 'time' : break; // TODO
        default :
          
          $this->getParser()->dspm(xt('Type %s inconnu dans l\'élément %s',
            new HTML_Strong($this->getName()), view($oInstance->getNode())), 'xml/error');
      }
      
      if (!$bResult) {
        
        if ($oInstance->useMessages()) $oInstance->addMessage(
          xt('Ce champ n\'est pas de type %s', new HTML_Strong($this->getName())), 'content');
          
        $oInstance->isValid(false);
      }
    }
    
    return $bResult;
  }
  
  public function isBasic() {
    
    return true;
  }
  
  public function isComplex() {
    
    return false;
  }
  
  public function isSimple() {
    
    return true;
  }
  
  public function getNamespace() {
    
    return $this->getParser()->getTargetNamespace();
  }
  
  public function hasRestrictions() {
    
    return false;
  }
  
  public function __toString() {
    
    return $this->getType();
  }
}


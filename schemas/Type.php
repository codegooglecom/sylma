<?php

require_once('abstract\Container.php');

class XSD_Type extends XSD_Container { // complex or simple, but defined
  
  private $aRestrictions = array();
  private $aAttributes = array();
  
  private $oBase = null;
  
  /**
   * @param XML_Element $oSource Node that represents this object in the schema (xs:complexType)
   * @param XSD_Particle|null $oParent Particle that contains this node
   * @param $oNode Unusefull for this class, due to extends
   * @param XSD_Parser $oParser Main parser, only necessary for root XSD_Node
   * @param array $aPath Array of element's name, parents of targeted element
   */
   
  public function __construct(XML_Element $oSource, $oParent, $oNode = null, XSD_Parser $oParser = null, array $aPath = array()) {
    
    parent::__construct($oSource, $oParent, $oNode, $oParser);
    
    $this->sPath = $oSource->getAttribute('name');
    $this->build($aPath);
  }
  
  private function build($aPath) {
    
    $oComponent = $this->getSource();
    
    $bComplexType = $oComponent->getName() != 'simpleType'; // WARNING : no name check for simpleType
    
    // WARNING : no check if text type node
    if (!$oComponent->hasChildren()) $this->dspm(xt('Elément enfants requis dans le type %s', view($oComponent)), 'xml/error');
    else {
      
      $bComplexContent = $bSimpleContent = false;
      
      if ($bComplexType && ($oFirst = $oComponent->getFirst())) {
        
        $bComplexContent = $bComplexType && $oFirst->getName() == 'complexContent';
        $bSimpleContent = $bComplexType && !$bComplexContent && $oFirst->getName() == 'simpleContent';
        
      } else $oFirst = $oComponent;
      
      if (!$bComplexType || $bComplexContent || $bSimpleContent)  { // simple type & complex type legacy
        
        if (!$oExtend = $oFirst->getFirst()) {
          
          $this->dspm(xt('Elément enfants (restriction|extension) requis dans %s', view($oComponent)), 'xml/error');
          
        }
        else if (!$sBase = $oExtend->getAttribute('base')) {
          
          $this->dspm(xt('Aucune base désigné pour l\'extension du composant %s', view($oComponent)), 'xml/error');
          
        }
        else { // valid
          
          $oType = $this->getParser()->getType($sBase, $oComponent);
          
          if ($bComplexType && $bComplexContent) { // complexContent
            
            // simple extension add to the end
            
            if ($oExtend->getFirst()) {
              
              if (!$oType->getSource()) {
                
                $this->dspm(xt('Le type %s ne peut pas étendre le type %s qui est invalide',
                  view($oComponent), view($oType->getSource())), 'xml/error');
                
              }
              else if ($oType->getSource()->getFirst()->getName() != $oExtend->getFirst()->getName()) {
                
                $this->dspm(xt('La particule de %s doit être identique à la particule de %s',
                  view($oType->getSource()), view($oExtend)), 'xml/error');
                
              }
              else {
                
                $oExtend->getFirst()->shift($oType->getSource()->getFirst()->getChildren());
                $this->setParticle($this->getParser()->create('particle', array($oExtend->getFirst(), $this, $aPath)));
              }
            }
            // $mResult = new XML_Element($oComponent->getName(), null, null, $this->getNamespace());
            
            // TODO $mResult->add($oType->getChildren(), $this->buildElement());
            
          }
          else { // simpleType & simpleContent
            
            $this->oBase = $oType;
            
            if ($oType->hasRestrictions()) { // if not empty type
              
              if ($oExtend->getName() != 'extension') { // restriction
                
                $this->aRestrictions = $oType->getRestrictions();
                $this->buildRestrictions($oExtend);
                
              }
              else { // extension
                
                // what TODO ?
                $this->dspm('TODO', 'xml/error');
              }
            }
            
            $this->buildRestrictions($oExtend);
          }
          
          //$mResult->add($oExtend);
        }
        
      }
      else { // complex type definition
        
        // WARNING : no check if valid children, if not group
        
        foreach ($oComponent->getChildren() as $oChild) {
          
          switch ($oChild->getName()) {
            
            case 'group' : 
              
              $this->setParticle($this->getParser()->create('group', array($oFirst, $this)));
              
            break;
            
            case 'sequence' :
            case 'choice' :
            case 'all' :
              
              $this->getParser()->pushType($this->getName());
              $this->setParticle($this->getParser()->create('particle', array($oFirst, $this, $aPath)));
              $this->getParser()->popType();
              
            break;
            
            case 'attribute' :
              
              $this->aAttributes[] = $this->getParser()->create('attribute', array($oChild, $this));
              
            break;
            
            default :
              
              if (!$this->isMixed()) {
                
                $this->dspm(xt('Erreur dans la définition de %s, élément %s inconnu', view($oComponent), view($oChild)));
              }
              
          }
        }
      }
    }
  }
  
  public function buildValue($mValue, $sBase) {
    
    switch ($sBase) {
      
      case 'string' : break;
      case 'decimal' : $mValue = floatval($mValue); break;
      case 'integer' : $mValue = intval($mValue); break;
      case 'boolean' : $mValue = strtobool($mValue); break;
      case 'date' : break; //$mValue = new Date();
      case 'time' : break;
      case 'base64Binary' : break;
    }
    
    return $mValue;
  }
  
  public function validate(XSD_Instance $oInstance, $aPath = array(), $bMessages = true) {
    
    $bResult = false;
    
    if ($this->isSimple()) {
      
      if ($oInstance->getNode()->isComplex()) {
        
        if ($this->useMessages()) $oInstance->addMessage(
          xt('L\'élément %s ne devrait pas être de type complexe mais %s',
          view($oInstance->getNode()), view($this->getSource())), 'content', 'invalid');
        
      } else if (!$bResult = $this->getBase()->validate($oInstance)) {
        
        if ($this->useMessages()) $oInstance->addMessage(
          xt('Cette valeur n\'est pas du type %s',
          new HTML_Strong($this->getBase())), 'content', 'invalid');
        
      } else {
        
        if ($this->hasRestrictions()) {
          
          // if ($oInstance->getName() == 'type_contrat') $this->dspm('yo', 'error');
          $mValue = $this->buildValue($oInstance->getValue(), $this->getBase()->getName());
          
          $aChoices = array('enumeration', 'pattern'); // must respect one of the values
          $bChoices = false;
          $bResult = true;
          
          foreach ($this->getRestrictions() as $aRestriction) {
            
            $mFacet = $this->buildValue($aRestriction[1], $this->getBase()->getName());
            $bSubResult = false;
            
            switch ($aRestriction[0]) {
              
              case 'minInclusive' : 
                
                $bSubResult = $mValue >= intval($mFacet);
                $sMessage = xt('La valeur doit être plus grande ou égale à %s', new HTML_Strong($mFacet));
                
              break;
                
              case 'maxInclusive' : 
                
                $bSubResult = $mValue <= intval($mFacet);
                $sMessage = xt('La valeur doit être plus petite ou égale à %s', new HTML_Strong($mFacet));
                
              break;
              case 'length' :
                
                $bSubResult = strlen($mValue) == $mFacet;
                $sMessage = xt('La chaîne doit comporter exactement %s caractères', new HTML_Strong($mFacet));
                
              break;
              case 'minLength' :
                
                $bSubResult = strlen($mValue) >= $mFacet;
                $sMessage = xt('La chaîne doit comporter au moins %s caractères', new HTML_Strong($mFacet));
                
              break;
              case 'maxLength' :
                
                $bSubResult = strlen($mValue) <= $mFacet;
                $sMessage = xt('La chaîne ne doit pas comporter plus de %s caractères', new HTML_Strong($mFacet));
                
              break;
              
              case 'enumeration' :
                
                $bSubResult = $mValue == $mFacet;
                $bChoices = true;
                
              break;
              
              case 'pattern' :
                
                $bSubResult = preg_match('/'.$mFacet.'/', $mValue);
                $bChoices = true;
                
              break;
            }
            
            if (in_array($aRestriction[0], $aChoices)) { // OR restrictions
              
              if ($bSubResult) {
                
                $bResult = $bSubResult;
                break;
              }
              
            } else if (!$bSubResult) { // AND restrictions
              
              if ($this->useMessages()) $oInstance->addMessage($sMessage, 'content', 'invalid');
              $bResult = $bSubResult;
            }
          }
          
          if (!$bResult) {
            
            if ($bChoices && $this->useMessages())
              $oInstance->addMessage(xt('Cette valeur n\'est pas autorisée'), 'content', 'invalid');
          }
        }
      }
      
    }
    else { // complex type
      
      if (!$this->getParser()->useType($this->getName())) { // avoid recursion
        
        if ($this->isMixed()) $bResult = true; // TODO real validation
        else {
          
          $this->getParser()->pushType($this->getName());
          
          if (!$oInstance->getParticle() && $this->keepValidate()) $oInstance->buildParticle(); // simple type should be complex
          
          if (!$this->getParticle()) {
            
            $this->dspm(xt('Impossible de valider l\'élément %s, particule inexistante', view($this->getSource())), 'xml/warning');
          }
          else { // ok, continue
            
            $bResult = $this->getParticle()->validate($oInstance->getParticle(), $aPath, $bMessages);
          }
          
          $this->getParser()->popType();
        }
        
      }
      else $bResult = true;
    }
    
    if (!$aPath) { // validate attributes
      
      foreach ($this->aAttributes as $oAttribute) {
        
        if (!$oAttribute->validate($oInstance, $bMessages)) $bResult = false;
      }
    }
    
    $oInstance->isValid($bResult);
    
    return $bResult;
  }
  
  public function isBasic() {
    
    return false;
  }
  
  public function isComplex() {
    
    return !$this->isSimple();
  }
  
  public function isSimple() {
    
    return (bool) $this->getBase();
  }
  
  public function isMixed() {
    
    return $this->getSource()->testAttribute('mixed', false);
  }
  
  public function hasRestrictions() {
    
    return (bool) $this->getRestrictions();
  }
  
  public function getBase() {
    
    return $this->oBase;
  }
  
  public function getNamespace($bReal = false) {
    
    if ($bReal) return $this->getParser()->getTargetNamespace();
    else return parent::getNamespace();
  }
  
  public function getName($bReal = false) {
    
    return ($bReal && $this->isNew() ? 'sylma-' : '').parent::getName();
  }
  
  public function getRestrictions() {
    
    return $this->aRestrictions;
  }
  
  private function buildRestrictions(XML_Element $oExtend) {
    
    // copy facets restriction
    foreach ($oExtend->getChildren() as $oChild) {
      
      $sValue = $oChild->hasAttribute('value') ? $oChild->getAttribute('value') : $oChild->read();
      
      if ($oChild->getName() != 'attribute') {
        
        $this->aRestrictions[] = array($oChild->getName(), $sValue);
        
      } else {
        
        $this->aAttributes[] = $this->getParser()->create('attribute', array($oChild, $this));
      }
    }
  }
  
  public function getClasses() {
    
    return array();
  }
  
  public function getPath() {
    
    return $this->getName();
  }
  
  public function parse() {
    
    $oResult = new XML_Element('base', null, array('name' => $this), $this->getNamespace());
    
    if ($this->isComplex()) {
      
      if ($this->isMixed()) $oResult->setAttribute('mixed', 'true');
      $oResult->setAttribute('lc:complex', 'true', $this->getNamespace());
    }
    
    if (!$oContent = $this->getParticle()) {
      
      if ($this->getRestrictions()) {
        
        $oContent = new XML_Element('restriction', null, null, $this->getNamespace());
        
        foreach ($this->getRestrictions() as $aRestriction) {
          
          $oContent->addNode($aRestriction[0], $aRestriction[1], null, $this->getNamespace());
        }
      }
      
      $oResult->setAttribute('type', $this->getBase());
    }
    
    $oResult->add($oContent);
    $oResult->add($this->aAttributes);
    
    return $oResult;
  }
  
  public function __toString() {
    
    return $this->getName(true);
  }
  
}


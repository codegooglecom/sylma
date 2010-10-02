<?php

class XSD_Parser extends Module {
  
  private $oResult = null;
  private $oSchema = null;
  
  private $aTypes = array();
  private $aGroups = array();
  private $iID = 1;
  
  public function __construct(XML_Document $oSchema, XML_Document $oDatas = null) {
    
    $this->setDirectory(__file__);
    
    $this->setNamespace('http://www.sylma.org/schemas', 'lc');
    $this->setNamespace(SYLMA_NS_XSD, 'xs', false);
    
    $this->iMoreDepth = null;
    $this->oResult = new XML_Document();
    
    $this->oSchema = $oSchema;
    
    $this->buildSchema($oDatas);
  }
  
  public function getSchema() {
    
    return $this->oSchema;
  }
  
  public function getID() {
    
    return $this->iID++;
  }
  
  private function buildSchema(XML_Document $oDatas = null) {
    
    $oRoot = $oDatas ? $oDatas->getRoot() : null;
    
    if ($oRoot && ($oElement = $this->getSchema()->get("/*/xs:element[@name='".$oRoot->getName()."']", $this->getNS()))) {
      
      $oElement = new XSD_Element($oElement, null, null, $this);
      $oSchemas = new XML_Element('schemas', array(
        $this->aTypes,
        $this->aGroups,
        new XSD_Model($oElement, $oRoot, null)), null, $this->getNamespace());
      
      $oResult = new XML_Element('sylma-schema', array($oDatas, $oSchemas), array(
        'xmlns:html' => SYLMA_NS_XHTML, 'xmlns:lc' => $this->getSchema()), $this->getNamespace());
      
    } else {
      
      foreach ($this->getSchema()->query("/*/xs:element", $this->getNS()) as $oElement) {
        
        // TODO, no valid root element or not at all
      }
    }
    
    $this->oResult->add($oResult);
  }
  
  public function addType($oType, $oElement) {
    
    $this->aTypes[$oElement->getNamespace()][$oType->getName()] = $oType;
    
    return $oType;
  }
  
  private function addGroup($oGroup, $oElement) {
    
    $this->aGroups[$oElement->getNamespace()][$oGroup->getName()] = $oGroup;
    
    return $oGroup;
  }
  
  public function getGroup($oElement, $oParent) {
    
    $mResult = null;
    $oSource = null;
    
    if ($sName = $oElement->getAttribute('ref')) { // reference
      
      if (array_key_exists($sName, $this->aGroups)) $mResult = $this->aGroups[$sName]; // ever indexed
      else if (!$oDefElement = $this->getSchema()->get("/*/xs:group[@name='$sName']")) {
        
        dspm(xt('Groupe %s introuvable dans le schéma', new HTML_Strong($sName), view($this->getSchema())), 'xml/error');
        
      } else { // group found
        
        $mResult = $this->addGroup(new XSD_Group($oDefElement, $oParent), $oElement); // new group
      }
      
    } else if ($oElement->hasChildren()) { // anonymous group
      
      $mResult = $this->addGroup(new XSD_Group($oElement, $oParent), $oElement);
      
      $oParent->cleanChildren();
      $oParent->setAttribute('ref', $mResult->getName());
      
    } else dspm(xt('Elément %s invalide dans %s', view($oElement), view($this->getSchema())), 'xml/error');
    
    return $mResult;
  }
  
  public function getType($sType, $oComponent) {
    
    $mResult = null;
    
    if ($iPrefix = strpos($sType, ':')) { // get namespace
      
      $sName = substr($sType, $iPrefix + 1);
      $sNamespace = $oComponent->getNamespace(substr($sType, 0, $iPrefix));
      
    } else { // TODO if qualified or not (get target namespace)
      
      $sNamespace = $oComponent->getNamespace(null);
    }
    
    if ($iPrefix && $sNamespace == SYLMA_NS_XSD) { // XMLSchema Base Datatypes
      
      $mResult = new XSD_BaseType($sType);
      
    } else { // Other namespaces datatypes
      
      if (array_key_exists($sNamespace, $this->aTypes) && array_key_exists($sType, $this->aTypes[$sNamespace])) { // ever seen
        
        $mResult = $this->aTypes[$sNamespace][$sType];
        
      } else { // new type
        
        $sTypes = "/*/xs:complexType[@name='$sType'] | /*/xs:simpleType[@name='$sType']";
        if (!$oElement = $this->getSchema()->get($sTypes, $this->getNS())) {
          
          dspm(xt('Type %s introuvable dans %s', new HTML_Strong($sType), view($this->getSchema())), 'xml/error');
          
        } else {
          
          $mResult = new XSD_Type($oElement, null, null, $this);
          $this->addType($mResult, $oComponent);
        }
      }
    }
    
    return $mResult;
  }
  
  public function parse() {
    
    return $this->oResult;
  }
}

class XSD_Basic {
  
  public function __construct(XML_Element $oSource, $oParent, $oNode = null, XSD_Parser $oParser = null) {
    
    $this->oParent = $oParent;
    $this->oParser = $oParser;
    $this->oSource = $oSource;
    $this->oNode = $oNode;
  }
  
  public function getParser() {
    
    return $this->oParser ? $this->oParser : $this->getParent()->getParser();
  }
  
  public function getParent() {
    
    return $this->oParent;
  }
  
  public function getNamespace() {
    
    return $this->getParser()->getNamespace();
  }
  
  public function getSource() {
    
    return $this->oSource;
  }
  
  public function getInstancables() {
    
    if ($this->getParent() instanceof XSD_Particle) $aResult[] = $this;
    else $aResult = $this->getParent()->getInstancables();
    
    return $aResult;
  }
  
  public function getPath() {
    
    if ($this->getParent()) return $this->getParent()->getPath();
    else return 'erreur';
  }
}

class XSD_Container extends XSD_Basic {
  
  private $sName = '';
  private $sPath = '';
  private $bNew = false;
  
  protected $oParticle = null;
  
  public function __construct(XML_Element $oSource, $oParent, $oNode = null, XSD_Parser $oParser = null) {
    
    parent::__construct($oSource, $oParent, $oNode, $oParser);
    
    if (!$this->sName = $oSource->getAttribute('name')) {
      
      $this->bNew = true;
      $this->sName = $this->sPath = str_replace('/', '-', $oParent->getPath());
    }
  }
  
  public function isNew() {
    
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
  
  public function getElement(XML_Element $oElement) {
    
    return $this->getParticle()->getElement($oElement);
  }
  
  public function getPath() {
    
    if (!$this->sPath) $this->sPath = ($this->getParent() ? $this->getParent()->getPath().'/' : '').$this->getName();
    
    return $this->sPath;
  }
}

class XSD_Node extends XSD_Container {
  
  private $oType = null;
  private $sID = '';
  
  public function __construct(XML_Element $oSource, $oParent, $oNode = null, XSD_Parser $oParser = null) {
    
    parent::__construct($oSource, $oParent, $oNode, $oParser);
    
    //$oElement = new XML_Element('model', null, array('id', $iID), $this->getNamespace());
    
    if ($sType = $oSource->getAttribute('type')) $this->oType = $this->getParser()->getType($sType, $oSource);
    else {
      
      if ($sRef = $oSource->getAttribute('ref')) {
        
        // ref
      } else {
        
        if (!$oFirst = $oSource->getFirst()) dspm(xt('Type indéfini pour le composant %s', view($oSource)), 'xml/error');
        else {
          
          $this->oType = new XSD_Type($oFirst, $this);
          $this->getParser()->addType($this->getType(), $oFirst); // WARNING : maybe bad $oFirst, may be the referencer
        }
      }
    }
  }
  
  public function getID() {
    
    return $this->sID;
  }
  
  public function setID($iID) {
    
    return $this->sID = (string) $iID;
  }
  
  public function getType() {
    
    return $this->oType;
  }
  
}

class XSD_Element extends XSD_Node {
  
  public function getParents() {
    
    return $this->getParent()->getInstancables();
  }
  
  public function parse() {
    
    $oResult = new XML_Element('element', null, array(
      'name'=> $this->getName(),
      'type' => $this->getType(),
      'id' => $this->getID()), $this->getNamespace());
    
    $oResult->cloneAttributes($this->getSource(), array('lc:title', 'lc:line-break'));
    
    return $oResult;
  }
}

class XSD_Attribute extends XSD_Node {
  
  public function parse() {
    
    return new XML_Element('attribute', null, array('name' => $this->getName(), 'type' => $this->getType()), $this->getNamespace());
  }
}

class XSD_Instance {
  
  private $oParent = null;
  private $oClass = null;
  
  private $aMessages = null;
  private $sStatut = '';
  
  public function __construct(XSD_Basic $oClass, XSD_Instance $oParent = null) {
    
    $this->oClass = $oClass;
    $this->oParent = $oParent;
  }
  
  protected function getMessages() {
  
    return $this->aMessages;
  }
  
  public function addMessage($mMessage, $sContext, $sStatut) {
    
    $this->aMessages[] = new XML_Element('message', $mMessage,
      array('context' => $sContext, 'statut' => $sStatut), $this->getNamespace());
  }
  
  public function setStatut($sStatut) {
    
    $this->sStatut = $sStatut;
  }
  
  public function getParent() {
    
    return $this->oParent;
  }
  
  public function getParser() {
    
    return $this->getClass()->getParser();
  }
  
  public function getNamespace() {
    
    return $this->getClass()->getNamespace();
  }
  
  public function getClass() {
    
    return $this->oClass;
  }
}

class XSD_Model extends XSD_Instance { // XSD_ElementInstance
  
  private $oElement = array();
  
  private $aChildren = array();
  private $aAttributes = array();
  private $aElements = array();
  private $oParticle = null;
  
  private $oType = null;
  private $sType = '';
  
  public function __construct(XSD_Element $oClass, XML_Element $oNode, XSD_Instance $oParent = null) {
    
    parent::__construct($oClass, $oParent);
    
    $this->oNode = $oNode;
    
    if ($oNode->isComplex()) {
      
      // complexType
      $this->buildParticle();
      
      if ($oNode->hasChildren()) $this->buildChildren();
      if ($oNode->hasAttributes()) $this->buildAttributes();
      
    } else {
      
      // simpleType
      $this->sType = $oClass->getSource()->getAttribute('type');
    }
    
    if ($this->getParent() && $this->getParent()->getParent() instanceof XSD_ParticleInstance) { // element ID
      
      if (!$sID = $this->getClass()->getID()) $this->getClass()->setID($this->getParser()->getID());
    }
    
    $this->validate();
  }
  
  public function validate() {
    
    $this->getClass()->getType()->validate($this);
  }
  
  private function buildParticle() {
    
    if ($this->getClass()) {
      
      if ($this->getClass()->getType()->isSimple()) { // node is mixed but type is simple
        
        // TODO : msg bad type
        $oParticle = new XSD_Particle(new XML_Element('sequence', null, null, $this->getNamespace()), null);
        $this->oParticle = new XSD_ParticleInstance($oParticle, $this);
        
      } else $this->oParticle = $this->getClass()->getType()->getParticle()->getInstance($this);
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
  
  private function buildChildren() {
    
    foreach ($this->getNode()->getChildren() as $oChild) {
      
      if ($oCurrent = $this->getClass()->getType()->getElement($oChild)) {
        //dspf($oCurrent->getParents());
        $this->getParticle()->add($oChild, $oCurrent->getParents());
      }
/*      {
        
        $aParents = array();
        while ($oCurrent = $oCurrent->getParent()) array_unshift($aParents, $oCurrent);
        
        $oParent = array_pop($aParents);*/
    }
  }
  
  private function buildAttributes() {
    
    // TODO
  }
  
  private function getAttributesMessages() {
    
    $aResult = array();
    foreach ($this->aAttributes as $oAttribute) $aResult = $oAttribute->getMessages();
    return $aResult;
  }
  
  public function parse() {
    
    $oModel = new XML_Element('model', array($this->getMessages(), $this->getAttributesMessages()), null, $this->getNamespace());
    
    $oModel->setAttribute('element', $this->getClass()->getID());
    
    if ($this->getNode()->isComplex()) { // complex type
      
      $oContent = $oModel->addNode('schema', null, null, $this->getNamespace());
      $oContent->add($this->getParticle());
      
      $oModel->setAttribute('base', $this->getClass()->getType());
      
    } else if ($this->getClass()->getType()->hasRestrictions()) { // simple type with restrictions
      
      $oModel->setAttribute('base', $this->getClass()->getType());
      
    } else { // base type
      
      $oModel->setAttribute('type', $this->getClass()->getType());
    }
    
    $iID = $this->getClass()->getParser()->getID();
    $this->getNode()->setAttribute('lc:model', $iID, $this->getNamespace());
    
    $oModel->addAttributes(array(
      'name' => $this->getClass()->getName(),
      'id' => $iID));
    
    return $oModel;
  }
}

class XSD_Particle extends XSD_Basic {
  
  private $aChildren = array();
  
  private $aElements = array();
  private $aParticles = array();
  
  public function __construct(XML_Element $oSource, $oParent, $oNode = null, XSD_Parser $oParser = null) {
    
    parent::__construct($oSource, $oParent, $oNode, $oParser);
    $this->indexChildren();
  }
  
  public function indexChildren() {
    
    $aResult = array();
    
    foreach ($this->getSource()->getChildren() as $oComponent) {
      
      $oResult = null;
      
      switch ($oComponent->getName()) {
        
        case 'group' :
          
          $oResult = new XSD_GroupReference($oComponent, $this);
          $this->aParticles[] = $oResult;
          
        break;
        
        case 'choice' :
        case 'sequence' :
          
          $oResult = new XSD_Particle($oComponent, $this);
          $this->aParticles[] = $oResult;
          
        break;
        
        case 'element' :
          
          $sName = $oComponent->hasAttribute('name') ? $oComponent->getAttribute('name') : $oComponent->getAttribute('ref');
          
          if (!$sName) dspm(xt('Aucun nom ou référence défini pour %s', view($oComponent)), 'xml/error');
          else {
            
            $oResult = new XSD_Element($oComponent, $this);
            $this->aElements[$sName] = $oResult;
          }
          
        break;
      }
      
      if ($oResult) $this->aChildren[] = $oResult;
    }
  }
  
  public function getParticles() {
    
    return $this->aParticles;
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
  
  public function getInstance($oParent) {
    
    return new XSD_ParticleInstance($this, $oParent);
  }
  
  public function parse() {
    
    $oParticle = new XML_Element($this->getSource()->getName(), $this->aChildren, null, $this->getNamespace());
    
    return $oParticle;
  }
}

class XSD_ParticleInstance extends XSD_Instance {
  
  private $aChildren = array();
  
  private $aParticles = array(); // child instance particles
  private $aElements = array(); // child instance elements
  
  public function add(XML_Element $oElement, array $aParents) {
    
    if ($aParents) {  // browse inside particles
      
      //$oParticle->add($oElement, $aParents);
      
      $oParent = array_pop($aParents);
      
      //dspf($this->getClass()->getParticles());
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
      
      if (!$oResult) dspm(xt('Erreur, particule %s introuvable dans le type', view($oParent)), 'xml/warning');
      else {
        
        $oResult->add($oElement, $aParents);
      }
      
    } else { // this one
      
      $this->aChildren[] = new XSD_Model($this->getClass()->getElement($oElement), $oElement, $this);;
    }
    
    
  }
  
  public function parse() {
    
    $oResult = new XML_Element($this->getClass()->getSource()->getName(), $this->aChildren, null, $this->getNamespace());
    
    // if first, no display
    if ($this->getParent() instanceof XSD_ParticleInstance) return $oResult;
    else return $oResult->getChildren();
  }
  
}

class XSD_Group extends XSD_Container {
  
  public function __construct(XML_Element $oSource, $oParent, $oNode = null, XSD_Parser $oParser = null) {
    
    parent::__construct($oSource, $oParent, $oNode, $oParser);
    
    $this->sPath = $oSource->getAttribute('name');
    $this->build();
  }
  
  private function build() {
    
    if (!$oFirst = $this->getSource()->getFirst()) {
      
      dspm('Impossible de construire le groupe, aucun enfant');
      
    } else {
      
      $this->oParticle = new XSD_Particle($oFirst, $this);
    }
  }
  
  public function getPath() {
    
    return $this->getName();
  }
  
  public function getElement(XML_Element $oElement) {
    
    return $this->getParticle()->getElement($oElement);
  }
  
  public function parse() {
    
    return new XML_Element('group', $this->getParticle(), array('name' => $this->getName()), $this->getNamespace());
  }
}

class XSD_GroupReference extends XSD_Basic {
  
  private $oGroup = null;
  
  public function __construct(XML_Element $oSource, $oParent, $oNode = null, XSD_Parser $oParser = null) {
    
    parent::__construct($oSource, $oParent, $oNode, $oParser);
    
    $this->oGroup = $this->getParser()->getGroup($oSource, $this);
  }
  
  public function getParticle() {
    
    return $this->getGroup()->getParticle();
  }
  
  public function getGroup() {
    
    return $this->oGroup;
  }
  
  public function getName() {
    
    return $this->getGroup()->getName();
  }
  
  public function getElement(XML_Element $oElement) {
    
    return $this->getGroup()->getElement($oElement);
  }
  
  public function getInstance($oParent) {
    
    return new XSD_GroupInstance($this, $oParent);
  }
  
  public function parse() {
    
    return new XML_Element('group', null, array('ref' => $this->getName()), $this->getNamespace());
  }
}

class XSD_GroupInstance extends XSD_Instance {
  
  private $oParticle = null;
  
  public function __construct($oClass, $oParent) {
    
    parent::__construct($oClass, $oParent);
    
    $this->oParticle = new XSD_ParticleInstance($oClass->getParticle(), $this);
  }
  
  private function getParticle() {
    
    return $this->oParticle;
  }
  
  public function add(XML_Element $oElement, array $aParents) {
    
    $this->getParticle()->add($oElement, $aParents);
  }
  
  public function parse() {
    
    $oGroup = new XML_Element('group', $this->getParticle(), array('name' => $this->getClass()->getName()), $this->getNamespace());
    
    return $oGroup;
  }
}

class XSD_BaseType {
  
  private $sType = '';
  
  public function __construct($sType) {
    
    $this->sType = $sType;
  }
  
  public function getName() {
    
    return substr($this->getType(), 3);
  }
  
  public function getType() {
    
    return $this->sType;
  }
  
  public function validate(XSD_Instance $oInstance) {
    
    $bResult = false;
    $mValue = $oInstance->getValue();
    
    if (!$mValue) $bResult = true;
    else {
      
      if (is_numeric($mValue)) {
        
        if (is_integer($mValue) || ctype_digit($mValue)) $sType = 'integer';
        else $sType = 'decimal';
        
      } else $sType = 'string';
      
      switch ($this->getName()) { // xs:string, xs:decimal, xs:integer, xs:boolean, xs:date, xs:time
        
        case 'string' :
        case 'integer' :
        case 'decimal' :
          
          $bResult = $sType == $this->getName();
          
        break;
        
        case 'boolean' : $bResult = in_array($mValue, array('1', '0', 'true', 'false')); break;
        case 'date' : break; // TODO pregmatch
        case 'time' : break; // TODO pregmatch
        default : dspm(xt('Le type %s n\'est pas reconnu', new HTML_Strong($this->getName())), 'xml/warning');
      }
    }
    
    return $bResult;
  }
  
  public function isBasic() {
    
    return true;
  }
  
  public function isSimple() {
    
    return true;
  }
  
  public function hasRestrictions() {
    
    return false;
  }
  
  public function __toString() {
    
    return $this->getType();
  }
}

class XSD_Type extends XSD_Container {
  
  private $aRestrictions = array();
  private $aAttributes = array();
  
  private $oBase = null;
  
  public function __construct(XML_Element $oSource, $oParent, $oNode = null, XSD_Parser $oParser = null) {
    
    parent::__construct($oSource, $oParent, $oNode, $oParser);
    
    $this->sPath = $oSource->getAttribute('name');
    $this->build();
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
  
  public function validate(XSD_Instance $oInstance) {
    
    $bResult = false;
    
    if ($this->isSimple()) {
      
      if ($oInstance->getNode()->isComplex()) {
        
        $oInstance->addMessage(xt('L\'élément %s devrait être du type simple %s',
          view($oInstance->getNode()), view($this->getSource())), 'content', 'badtype');
        
      } else if (!$bResult = $this->getBase()->validate($oInstance)) {
        
        $oInstance->addMessage(xt('Cette valeur n\'est pas du type %s', new HTML_Strong($this->getBase())), 'content', 'invalid');
        
      } else {
        
        if ($this->hasRestrictions()) {
          
          $mValue = $this->buildValue($oInstance->getValue(), $this->getBase()->getName());
          
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
              
              case 'enumeration' : $bSubResult = $mValue == $mFacet; break;
              case 'pattern' : $bSubResult = preg_match('/'.$mFacet.'/', $mValue); break;
            }
            
            if (in_array($aRestriction[0], array('enumeration', 'pattern'))) { // OR restrictions
              
              if ($bSubResult) {
                
                $bResult = $bSubResult;
                break;
              }
              
            } else if (!$bSubResult) { // AND restrictions
              
              $oInstance->addMessage($sMessage, 'content', 'invalid');
              $bResult = $bSubResult;
            }
          }
          
          if (!$bResult) $oInstance->setStatut('invalid');
        }
      }
      
    } else {
      
      // TODO : complex
    }

    
    return $bResult;
  }
  
  public function isBasic() {
    
    return false;
  }
  
  public function isSimple() {
    
    return (bool) $this->getBase();
  }
  
  public function hasRestrictions() {
    
    return (bool) $this->getRestrictions();
  }
  
  public function getBase() {
    
    return $this->oBase;
  }
  
  public function getName($bReal = false) {
    
    return ($bReal && $this->isNew() ? 'sylma-' : '').parent::getName();
  }
  
  public function getRestrictions() {
    
    return $this->aRestrictions;
  }
  
  private function build() {
    
    $oComponent = $this->getSource();
    
    $bComplexType = $oComponent->getName() != 'simpleType'; // WARNING : no name check for simpleType
    
    // WARNING : no check if text type node
    if (!$oComponent->hasChildren()) dspm(xt('Elément enfants requis dans le type %s', view($oComponent)), 'xml/error');
    else {
      
      $bComplexContent = $bSimpleContent = false;
      
      if ($bComplexType && ($oFirst = $oComponent->getFirst())) {
        
        $bComplexContent = $bComplexType && $oFirst->getName() == 'complexContent';
        $bSimpleContent = $bComplexType && !$bComplexContent && $oFirst->getName() == 'simpleContent';
        
      } else $oFirst = $oComponent;
      
      if (!$bComplexType || $bComplexContent || $bSimpleContent)  { // simple type & complex type legacy
        
        if (!$oExtend = $oFirst->getFirst()) {
          
          dspm(xt('Elément enfants (restriction|extension) requis dans %s', view($oComponent)), 'xml/error');
          
        } else if (!$sBase = $oExtend->getAttribute('base')) {
          
          dspm(xt('Aucune base désigné pour l\'extension du composant %s', view($oComponent)), 'xml/error');
          
        } else { // valid
          
          $oType = $this->getParser()->getType($sBase, $oComponent);
          $this->oBase = $oType;
          
          if ($bComplexType && $bComplexContent) { // complexContent
            
            
            // $mResult = new XML_Element($oComponent->getName(), null, null, $this->getNamespace());
            
            // TODO $mResult->add($oType->getChildren(), $this->buildElement());
            
          } else { // simpleType & simpleContent
            
            if ($oType->hasRestrictions()) { // if not empty type
              
              if ($oExtend->getName() != 'extension') { // restriction
                
                $this->aRestrictions = $oType->getRestrictions();
                $this->buildRestrictions($oExtend);
                
              } else { // extension
                
                // what TODO ?
              }
            }
            
            $this->buildRestrictions($oExtend);
          }
          
          //$mResult->add($oExtend);
        }
        
      } else { // complex type definition
        
        if ($oFirst->getName() == 'group') $this->setParticle(XSD_Group($oFirst, $this));
        else $this->setParticle(new XSD_Particle($oFirst, $this));
      }
    }
  }
  
  private function buildRestrictions(XML_Element $oExtend) {
    
    // copy facets restriction
    foreach ($oExtend->getChildren() as $oChild) {
      
      $sValue = $oChild->hasAttribute('value') ? $oChild->getAttribute('value') : $oChild->read();
      
      if ($oChild->getName() != 'attribute') {
        
        $this->aRestrictions[] = array($oChild->getName(), $sValue);
        
      } else {
        
        $this->aAttributes[] = new XSD_Attribute($oChild, $this);
      }
    }
  }
  
  public function getInstancables() {
    
    return array();
  }
  
  public function getPath() {
    
    return $this->getName();
  }
  
  public function parse() {
    
    $oResult = new XML_Element('base', null, array('name' => $this), $this->getNamespace());
    
    if (!$this->isSimple()) $oResult->setAttribute('complex', 'true');
    
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


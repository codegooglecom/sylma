<?php

class XSD_Parser extends Module {
  
  private $aOptions = array(
    'model' => false, // if false, it will not build the model
    'messages' => true, // if false, validation and building will stop at first error
    'is-valid' => true, // valid or not
    'mark' => true, // copy sylma schema namespaced attributes to elements
    'load-refs' => false); // load key-refs datas, require database
  
  private $oModel = null;
  
  private $aMessages = array();
  private $aTypes = array();
  public $aTypesUsed = array(); // stack of types to avoid recursive calls
  private $aGroups = array();
  private $aRefs = array();
  private $iID = 1;
  
  private $oElement = null;
  
  public function __construct(XML_Document $oSchema, XML_Document $oDatas = null, $aOptions = array()) {
    
    $this->setName('schemas');
    $this->setDirectory(__file__);
    
    $this->setNamespace(SYLMA_NS_SCHEMAS, 'lc');
    $this->setNamespace(SYLMA_NS_XSD, 'xs', false);
    
    if ($aOptions) $this->aOptions = array_merge($this->aOptions, $aOptions);
    
    if ($this->oSchema = $oSchema) $this->oModel = $this->buildSchema($oDatas);
  }
  
  private function getOption($sKey) {
    
    if (array_key_exists($sKey, $this->aOptions)) return $this->aOptions[$sKey];
    else return null;
  }
  
  public function getSchema() {
    
    return $this->oSchema;
  }
  
  /*
   * Namespace that should use the document to validate
   **/
  public function getTargetNamespace() {
    
    return $this->getSchema()->getAttribute('targetNamespace');
  }
  
  /*
   * Generate a random ID number incremented each time
   * @return int
   **/
  public function getID() {
    
    return $this->iID++;
  }
  
  private function buildSchema(XML_Document $oDatas = null) {
    
    $oResult = null;
    $oRoot = $oDatas ? $oDatas->getRoot() : null;
    
    if ($sPath = $this->getOption('path')) $aPath = explode('/', $sPath);
    else $aPath = array();
    
    $sRoot = $aPath ? $aPath[0] : $oRoot->getName();
    
    if ($oRoot && ($oSource = $this->getSchema()->get("/*/xs:element[@name='".$sRoot."']", $this->getNS()))) {
      
      $oElement = new XSD_Element($oSource, null, null, $this, $aPath);
      
      if ($sPath) $oElement = $this->getElement();
      
      if (!$oElement) {
        
        dspm('Element manquant');
        
      } else {
        
        $oModel = $oElement->getInstance(null, $oRoot);
        
        $this->isValid($oElement->validate($oModel, $aPath));
        
        if ($this->keepValidate()) {
          
          $oSchemas = new XML_Element('schemas', array(
            $this->aRefs,
            $this->aTypes,
            $this->aGroups,
            $oModel), null, $this->getNamespace());
          
          $oResult = new XML_Element('sylma-schema', array($oDatas, $oSchemas), array(
            'xmlns:html' => SYLMA_NS_XHTML, 'xmlns:lc' => $this->getNamespace()), $this->getNamespace());
        }
      }
    } else {
      
      $this->isValid(false);
      $this->dspm('no root', 'action/error');
      
      foreach ($this->getSchema()->query("/*/xs:element", $this->getNS()) as $oElement) {
        // TODO, no valid root element or not at all
      }
    }
    
    if ($oResult) return new XML_Document($oResult);
    else return null;
  }
  
  public function useType($sType) {
    
    return in_array($sType, $this->aTypesUsed);
  }
  
  public function pushType($sType) {
    
    return array_push($this->aTypesUsed, $sType);
  }
  
  public function popType() {
    
    return array_pop($this->aTypesUsed);
  }
  
  public function addType($oType, $oElement) {
    
    $this->aTypes[$oElement->getNamespace()][$oType->getName()] = $oType;
    
    return $oType;
  }
  
  private function addGroup($oGroup, $oElement) {
    
    $this->aGroups[$oElement->getNamespace()][$oGroup->getName()] = $oGroup;
    
    return $oGroup;
  }
  
  public function addRef($oElement) {
    
    $mRef = $oElement->getAttribute('lc:key-ref');
    
    // replace document() calls
    
    if (preg_match_all("`document\('((db)://|(file)://)?([^']+)'\)`", $mRef, $aDocuments, PREG_OFFSET_CAPTURE)) {
      
      if (!$sPath = $aDocuments[4][0][0]) $this->dspm(xt('Ouverture de document %s invalide', new HTML_Strong($sSelect)), 'xml/warning');
      else {
        
        if ($aDocuments[2][0][0] == 'db') { // call to database
          
          if (Controler::getDatabase()) {
            
            $sDocument = "document('".Controler::getDatabase()->getCollection()."/".$aDocuments[4][0][0]."')";
            $sFullPath = substr_replace($mRef, $sDocument, $aDocuments[0][0][1], strlen($aDocuments[0][0][0])); 
            
            if ($this->getOption('load-refs')) { // replace ref with corresponding nodes
              
              if (preg_match_all('/(w+):[\w-_]+/', $sPath, $aPrefixes)) { // match prefixes
                
                dspf($aPrefixes); // TODO add prefixes
              }
              
              if ($oRefResult = Controler::getDatabase()->get($sFullPath)) $mRef = $oRefResult;
              else $this->dspm(xt('La référence %s n\'est pas valide', new HTML_Strong($sPath)), 'xml/warning');
              
            } else $mRef = $sFullPath; // just set real path
          }
          
        } else if ($aDocuments[2][0][0] == 'file') {
          
          $this->dspm('Chargement de document pas encore prêt', 'xml/warning'); // TODO
          $sPath = Controler::getAbsolutePath($sPath, (string) $this->getFile()->getParent());
          
          // dspf($sPath);
          // $this->dspm($this->getFile());
          /*if (!$oFile = Controler::getFile($aDocuments[2][0]), $this->getFile()->getParent()) {
            
            $this->dspm(xt('Ouverture de document %s invalide', new HTML_Strong($sView)), 'warning');
          } else {
          
          }*/
          //dspf($oFile);
        } else {
          
          // no root
        }
      }
    }
    
    $oRef = new XML_Element('key-ref',
      $mRef,
      array(
        'name' => $oElement->getAttribute('name'),
        'full-name' => $oElement->getAttribute('name')), $this->getNamespace());
    
    $oRef->cloneAttributes($oElement, array('ref-constrain', 'ref-view'), $this->getNamespace());
    
    $this->aRefs[] = $oRef;
  }
  
  public function getGroup($oElement, $oParent) {
    
    $mResult = null;
    $oSource = null;
    
    if ($sName = $oElement->getAttribute('ref')) { // reference
      
      if (array_key_exists($sName, $this->aGroups)) $mResult = $this->aGroups[$sName]; // ever indexed
      else if (!$oDefElement = $this->getSchema()->get("/*/xs:group[@name='$sName']")) {
        
        $this->dspm(xt('Groupe %s introuvable dans le schéma', new HTML_Strong($sName), view($this->getSchema())), 'xml/error');
        
      } else { // group found
        
        $mResult = $this->addGroup(new XSD_Group($oDefElement, $oParent), $oElement); // new group
      }
      
    } else if ($oElement->hasChildren()) { // anonymous group
      
      $mResult = $this->addGroup(new XSD_Group($oElement, $oParent), $oElement);
      
      $oParent->cleanChildren();
      $oParent->setAttribute('ref', $mResult->getName());
      
    } else $this->dspm(xt('Elément %s invalide dans %s', view($oElement), view($this->getSchema())), 'xml/error');
    
    return $mResult;
  }
  
  public function getType($sType, $oComponent, $aPath = array()) {
    
    $mResult = null;
    
    if ($iPrefix = strpos($sType, ':')) { // get namespace
      
      $sName = substr($sType, $iPrefix + 1);
      $sNamespace = $oComponent->getNamespace(substr($sType, 0, $iPrefix));
      
    } else { // TODO if qualified or not (get target namespace)
      
      $sNamespace = $oComponent->getNamespace(null);
    }
    
    if ($iPrefix && $sNamespace == SYLMA_NS_XSD) { // XMLSchema Base Datatypes
      
      $mResult = new XSD_BaseType($sType, $this);
      
    } else { // Other namespaces datatypes
      
      if (array_key_exists($sNamespace, $this->aTypes) && array_key_exists($sType, $this->aTypes[$sNamespace])) { // ever seen
        
        $mResult = $this->aTypes[$sNamespace][$sType];
        
      } else { // new type
        
        $sTypes = "/*/xs:complexType[@name='$sType'] | /*/xs:simpleType[@name='$sType']";
        if (!$oElement = $this->getSchema()->get($sTypes, $this->getNS())) {
          
          $this->dspm(xt('Type %s introuvable dans %s', new HTML_Strong($sType), view($this->getSchema())), 'xml/error');
          
        } else {
          
          $mResult = new XSD_Type($oElement, null, null, $this, $aPath);
          $this->addType($mResult, $oComponent);
        }
      }
    }
    
    return $mResult;
  }
  
  public function setElement($oElement) {
    
    $this->oElement = $oElement;
  }
  
  public function getElement() {
    
    return $this->oElement;
  }
  
  public function isValid($bValid = null) {
    
    if ($bValid !== null) $this->aOptions['is-valid'] = $bValid;
    return $this->getOption('is-valid');
  }
  
  public function keepValidate() {
    
    return $this->useModel() || $this->useMessages() || $this->isValid();
  }
  
  public function useMark() {
    
    return $this->getOption('mark');
  }
  
  public function useModel() {
    
    return $this->getOption('model');
  }
  
  /*** Messages ***/
  
  public function useMessages() {
    
    return $this->getOption('messages');
  }
  
  public function getMessages() {
    
    return $this->aMessages;
  }
  
  public function addMessages($aMessages) {
    
    $this->aMessages += $aMessages;
  }
  
  public function addMessage($oSource, $oMessage) {
    
    $this->aMessages[] = array($oSource, $oMessage);
  }
  
  public function parse() {
    
    return $this->oModel;
  }
}

abstract class XSD_Basic {
  
  protected $sPath = '';
  
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
  
  protected function dspm($mMessage, $sStatut = SYLMA_MESSAGES_DEFAULT_STAT) {
    
    $sPath = xt('Schema : %s', $this->getParser()->getSchema()->getFile()->parse());
    
    return dspm(array($sPath, new HTML_Tag('hr'), $mMessage), $sStatut);
  }
}

abstract class XSD_Container extends XSD_Basic {
  
  private $sName = '';
  private $bNew = false; // anonymous definition
  
  protected $oParticle = null;
  
  public function __construct(XML_Element $oSource, $oParent, $oNode = null, XSD_Parser $oParser = null) {
    
    parent::__construct($oSource, $oParent, $oNode, $oParser);
    
    if (!$this->sName = $oSource->getAttribute('name')) {
      
      $this->bNew = true;
      $this->sName = $this->sPath = str_replace('/', '-', $oParent->getPath());
    }
  }
  
  protected function isNew() { // Classes : type, group
    
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
  
  public function getPath() { // Classes : [type], [group]
    
    if (!$this->sPath) $this->sPath = ($this->getParent() ? $this->getParent()->getPath().'/' : '').$this->getName();
    
    return $this->sPath;
  }
  
  public function getElement(XML_Element $oElement) { // Instances : model, [particle], group
    
    return $this->getParticle()->getElement($oElement);
  }
}

abstract class XSD_Node extends XSD_Container {
  
  private $oType = null;
  private $sID = '';
  
  private $iMax = 1;
  private $iMin = 1;
  
  protected $aInstances = array(); // instanced particles derived from this particle
  
  abstract public function buildInstance(XSD_Instance $oParent);
  
  /*
   * @param XML_Element $oSource Node that represents this object in the schema (xs:element, xs:attribute)
   * @param XSD_Particle|null $oParent Particle that contains this node
   * @param $oNode Unusefull for this class, due to extends
   * @param XSD_Parser $oParser Main parser, only necessary for root XSD_Node
   * @param array $aPath Array of element's name, parents of targeted element
   */
  
  public function __construct(XML_Element $oSource, $oParent, $oNode = null, XSD_Parser $oParser = null, $aPath = array()) {
    
    parent::__construct($oSource, $oParent, $oNode, $oParser);
    
    if (count($aPath) == 1) $this->getParser()->setElement($this);
    array_shift($aPath);
    
    // if parent sequence is not direct child of element, build parent name and get it
    if ($this->getParent() && $this->getParent()->getParent() instanceof XSD_Particle) $this->sID = $this->getParser()->getID();
    
    $sType = $oSource->getAttribute('type');
    
    if ($oSource->hasAttribute('minOccurs')) $this->iMin = intval($oSource->getAttribute('minOccurs'));
    if ($oSource->hasAttribute('maxOccurs')) $this->iMax = intval($oSource->getAttribute('maxOccurs'));
    
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
          
          $this->oType = new XSD_Type($oFirst, $this, null, null, $aPath);
          $this->getParser()->addType($this->getType(), $oFirst); // WARNING : maybe bad $oFirst, may be the referencer
        }
      }
      
    } else {
      
      if ($sType) $this->oType = $this->getParser()->getType($sType, $oSource, $aPath);
      else $this->dspm(xt('Aucun type défini pour %s', view($oSource)), 'xml/warning');
    }
  }
  
  public function getID() {
    
    return $this->sID;
  }
  
  public function isRequired() {
    
    return (bool) $this->iMin;
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

abstract class XSD_Class extends XSD_Basic {
  
  protected $aInstances = array(); // instanced particles derived from this particle
  
  private $iMax = 1;
  private $iMin = 1;
  
  abstract public function getInstance($oParent);
  //abstract protected function buildInstance(XSD_Instance $oParent);
  
  public function isRequired() {
    
    return (bool) $this->iMin;
  }
  
  public function getInstances() {
    
    return $this->aInstances;
  }
  
  protected function hasInstance(XSD_Instance $oNeedle) {
    
    foreach ($this->aInstances as $oInstance) if ($oNeedle === $oInstance) return true;
    
    return false;
  }
}

abstract class XSD_Instance {
  
  private $oParent = null;
  private $oClass = null;
  
  private $aMessages = array();
  private $sStatut = '';
  
  public function __construct(XSD_Basic $oClass, XSD_Instance $oParent = null) {
    
    $this->oClass = $oClass;
    $this->oParent = $oParent;
  }
  
  protected function getMessages() {
  
    return $this->aMessages;
  }
  
  public function addMessage($mMessage, $sContext, $sStatut = 'invalid') {
    
    $oMessage = null;
    
    if ($this->useMessages()) {
      
      $oMessage = new XML_Element('message', $mMessage,
        array('context' => $sContext, 'statut' => $sStatut), $this->getNamespace());
      
      $this->getParser()->addMessage($this, $oMessage);
      $this->aMessages[] = $oMessage;
    }
    
    //return $oMessage;
  }
  
  public function addMessages($aMessages) {
    
    $this->aMessages += $aMessages;
  }
  
  public function useMessages() { // TODO : replicate of XSD_Basic
    
    return $this->getParser()->useMessages();
  }
  
  public function keepValidate() { // TODO : replicate of XSD_Basic
    
    return $this->getParser()->keepValidate();
  }
  
  public function getStatut() {
    
    return $this->sStatut;
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
  
  public function getModel() {
    
    return $this->getParent()->getModel();
  }
  
  public function getName() {
    
    return $this->getClass()->getName();
  }
  
  public function getNode() {
    
    return $this->getModel()->getNode();
  }
  
  public function getNamespace() {
    
    return $this->getClass()->getNamespace();
  }
  
  public function getClass() {
    
    return $this->oClass;
  }
}

/*** Real Classes ***/

class XSD_Element extends XSD_Node {
  
  public function getParents() {
    
    return $this->getParent()->getClasses();
  }
  
  public function validate(XSD_Instance $oInstance, $aPath = array()) {
    
    $bResult = null;
    
    if (!$bResult = $this->getType()->validate($oInstance, $aPath)) $oInstance->setStatut('invalid');
    
    return $bResult;
  }
  
  public function buildInstance(XSD_Instance $oParent, XML_Element $oPrevious = null) {
    
    $oInstance = null;
    
    if ($this->getType()) {
      
      $oElement = $oParent->getModel()->getNode()->insertChild(
        new XML_Element($this->getName(), null, null, $this->getType()->getNamespace(true)),
        $oPrevious, true);
      
      if ($sDefault = $this->getSource()->getAttribute('default')) {
        
        $oElement->set($sDefault);
        
      } else if ($sDefault = $this->getSource()->getAttribute('default-query', $this->getNamespace())) {
        
        if (!SYLMA_USE_DB) $this->dspm('Impossible de déterminer la valeur par défaut. XQuery est nécessaire', 'xml/warning');
        else $oElement->set(Controler::getDatabase()->query($sDefault));
      }
      
      $oInstance = $this->getInstance($oParent, $oElement);
      $oParent->insert($oInstance);
    }
    
    return $oInstance;
  }
  
  public function getInstance($oParent, $oNode) {
    
    $oModel = new XSD_Model($this, $oNode, $oParent);
    if (!$this->isRequired()) $oModel->setStatut('optional');
    
    return $this->addInstance($oModel);
  }
  
  public function parse() {
    
    $oResult = new XML_Element('element', null, array(
      'name'=> $this->getName(),
      'type' => $this->getType(),
      'id' => $this->getID()), $this->getNamespace());
    
    return $oResult;
  }
}

class XSD_Attribute extends XSD_Node {
  
  public function buildInstance(XSD_Instance $oParent) {
    
    return null; // TODO
  }
  
  public function parse() {
    
    return new XML_Element('attribute', null, array('name' => $this->getName(), 'type' => $this->getType()), $this->getNamespace());
  }
}

class XSD_Model extends XSD_Instance { // XSD_ElementInstance
  
  private $oParticle = null;
  private $oNode = null;
  
  public function __construct(XSD_Element $oClass, XML_Element $oNode = null, XSD_Instance $oParent = null) {
    
    parent::__construct($oClass, $oParent);
    
    $this->oNode = $oNode;
    
    if ($oNode) {
      
      if ($oNode->isComplex()) {
        
        // complexType
        $this->buildParticle();
        
        if ($oNode->hasChildren()) $this->buildChildren();
        if ($oNode->hasAttributes()) $this->buildAttributes();
      }
      
    } else $this->setStatut('missing');
  }
  
  public function buildParticle() {
    
    if (!$this->getClass()) $this->dspm(xt('Aucun élément classe défini pour %s', view($this)), 'xml/error');
    else {
      
      if ($this->getClass()->getType()->isComplex()) { // complex type
        
        if (!$this->getClass()->getType()->getParticle()) { // node is complex but type is simple
          
          $this->dspm(xt('Impossible de construire l\'élément %s, particule manquante', view($this->getNode())), 'xml/warning');
          
        } else {
          
          if (!$this->getClass()->getType()->isMixed()) // complex not mixed
            $this->oParticle = $this->getClass()->getType()->getParticle()->getInstance($this);
        }
        
      } else { // simple type
        
        if ($this->getNode()) { // node is mixed but type is simple
          
          if ($this->useMessages()) $this->addMessage(
            xt('L\'élément %s ne devrait pas contenir d\'autre éléments, le type %s est attendu',
            view($this->getNode()), view($this->getClass()->getSource())), 'content', 'badtype');
          else $this->getParser()->isValid(false);
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
  
  private function buildChildren() {
    
    if ($this->getParticle()) {
      
      foreach ($this->getNode()->getChildren() as $oChild) {
        
        if (($oCurrent = $this->getClass()->getType()->getElement($oChild))) {
          
          $this->getParticle()->add($oChild, $oCurrent->getParents());
          
        } else {
          
          if ($this->useMessages()) $this->addMessage(
            xt('L\'élément %s n\'est pas autorisé au sein de l\'élément %s',
            view($oChild->getName()), view($this->getClass()->getName())), 'element', 'denied');
          
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
        
        $oAttributes = $this->getClass()->getSource()->query("@*[namespace-uri()='{$this->getNamespace()}']");
        $this->getNode()->add($oAttributes);
      }
      
      $this->getNode()->setAttribute('lc:model', $iID, $this->getNamespace());
      
      if ($this->getParticle() || $this->getNode()->isComplex()) { // complex type or complex node
        
        $oModel->cloneAttributes($this->getClass()->getSource(), array('minOccurs', 'maxOccurs'));
        // dspf($this->getNode());
        // dspf($this->getClass()->getSource());
        // dspf('-------------');
        $oContent = $oModel->addNode('schema', null, null, $this->getNamespace());
        $oContent->add($this->getParticle());
        
        $oModel->setAttribute('base', $this->getClass()->getType());
        
      } else if ($this->getClass()->getType()->hasRestrictions()) { // simple type with restrictions
        
        $oModel->setAttribute('base', $this->getClass()->getType());
        
      } else { // base type
        
        $oModel->setAttribute('type', $this->getClass()->getType());
      }
      
      if ($this->getMessages()) $oModel->shift(new XML_Element('annotations', $this->getMessages(), null, $this->getNamespace()));
    }
    
    return $oModel;
  }
}

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
    
    $sPath = array_last($aPath);
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
            
            $oResult = new XSD_Element($oComponent, $this, null, null, $aPath);
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
  
  public function validate(XSD_Instance $oInstance, $aPath = array()) {
    
    $sPath = array_last($aPath);
    
    if (!$oInstance) { // temp ?
      
      $this->dspm(xt('Aucun instance reçue pour valider l\'élément %s', view($this->getSource())), 'xml/error');
      return false;
    }
    
    $bResult = true;
    $oPrevious = null;
    
    $iShift = 0;
    
    $aSubInstances = $oInstance->getChildren();
    list(,$oSubInstance) = each($aSubInstances);
    
    // TODO, if sequence
    if ($this->isRequired()) {
      
      foreach ($this->getChildren() as $oChild) {
        
        if ($oSubInstance && $oChild->hasInstance($oSubInstance)) {
          
          $oInstance->shiftSeek();
          
          if (!$oChild->validate($oSubInstance, $aPath)) {
            
            $bResult = $this->getParser()->isValid(false);
            if (!$this->keepValidate()) break; // do not control next children if invalid and parser is in validation mode
          }
          
          $oPrevious = $oSubInstance;
          list(,$oSubInstance) = each($aSubInstances);
          
        } else {
          
          if ($oChild->getSource()->testAttribute('editable', true, $this->getNamespace())) {
            
            if ($oChild->isRequired()) { // if required validation fails
              
              $bResult = $this->getParser()->isValid(false);
              if (!$this->keepValidate()) break;
            }
            
            if ($this->getParser()->useModel()) {
              
              $oNode = $oPrevious ? $oPrevious->getNode() : null;
              
              $oNewInstance = $oChild->buildInstance($oInstance, $oNode); // TODO occurs
              $oChild->validate($oNewInstance, $aPath);
              
              if (!$oChild->isRequired()) { // if not required
                
                $oNewInstance->setStatut('optional');
                
              } else if ($oNewInstance && $oInstance->useMessages()) { // if required set message and statut
                
                $oNewInstance->setStatut('missing');
                $oNewInstance->addMessage(xt('Ce champ doit être indiqué'), 'content', 'invalid');
              }
              
              $oPrevious = $oNewInstance;
            }
          }
        }
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
    // dspf($oInstance->getName());
    // dspf($this->getSeek());
    // dsparray($this->getChildren());
    if ($iIndex === null || $iIndex == count($aChildren)) $this->aChildren[] = $oInstance;
    else if (!$iIndex) array_unshift($this->aChildren, $oInstance);
    else $this->aChildren = array_merge(array_slice($aChildren, 0, $iIndex), array($oInstance), array_slice($aChildren, $iIndex));
    
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

class XSD_Group extends XSD_Container {
  
  public function __construct(XML_Element $oSource, $oParent, $oNode = null, XSD_Parser $oParser = null) {
    
    parent::__construct($oSource, $oParent, $oNode, $oParser);
    
    $this->sPath = $oSource->getAttribute('name');
    $this->build();
  }
  
  private function validate($oInstance) {
    
    $this->getParticle()->validate($oInstance->getParticle());
  }
  
  private function getInstance(XSD_Instance $oParent) {
    
    // TODO occurs
    $oElement = new XML_Element('group', null, array('ref' => $this->getName()), $this->getNamespace());
    $oGroupRef = new XSD_GroupReference($oElement, $oParent);
    
    return $oGroupRef->getInstance($oParent); 
  }
  
  private function build() {
    
    if (!$oFirst = $this->getSource()->getFirst()) {
      
      $this->dspm(xt('Impossible de construire le groupe %s, car il ne possède aucun enfant', view($this->getSource())), 'xml/error');
      
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

class XSD_GroupReference extends XSD_Class {
  
  private $oGroup = null;
  
  public function __construct(XML_Element $oSource, $oParent) {
    
    parent::__construct($oSource, $oParent, $oNode, $oParser);
    
    $this->oGroup = $this->getParser()->getGroup($oSource, $this);
  }
  
  public function getParticle() {
    
    return $this->getGroup()->getParticle();
  }
  
  public function validate($oInstance) {
    
    // TODO occurences
    
    if ($oInstance && $this->hasInstance($oInstance)) {
      
      $bResult = $this->getGroup()->validate($oInstance);
      
    } else {
      
      $bResult = false;
      
      if ($this->useMessages()) $oInstance->addMessage(
        xt('Le groupe %s est manquant dans %s',
        new HTML_Strong($this->getClass()->getName()), view($this->getName())), 'content', 'invalid');
      
      if ($this->keepValidate()) $this->buildInstance($oInstance->getParent()); // DEBUG
    }
    
    return $bResult;
  }
  
  protected function buildInstance(XSD_Instance $oParent) {
    
    $oInstance = $this->getInstance($oParent);
    $oInstance->validate();
    
    $oParent->insert($oInstance);
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
  
  public function validate(XSD_Instance $oInstance, $aPath = array()) {
    
    $bResult = false;
    $mValue = $oInstance->getValue();
    
    switch ($this->getName()) {
      
      case 'string' : $bResult = is_string($mValue); break; // && !is_numeric($mValue)
      case 'date' : $bResult = preg_match('/^\d{4}-\d{2}-\d{2}$/', $mValue); break;
      case 'dateTime' : break;
      case 'duration' : break;
      case 'boolean' : $bResult = in_array($mValue, array('1', '0', 'true', 'false')); break;
      case 'integer' : $bResult = is_integer($mValue) || ctype_digit($mValue); break;
      case 'decimal' : $bResult = is_numeric($mValue) && !is_integer($mValue) && !ctype_digit($mValue); break;
      case 'time' : break; // TODO
      default :
        
        $this->getParser()->dspm(xt('Type %s inconnu dans l\'élément %s',
          new HTML_Strong($this->getName()), view($oInstance->getNode())), 'xml/error');
    }
    
    if (!$bResult && $oInstance->useMessages())
      $oInstance->addMessage(xt('Ce champ n\'est pas de type %s', new HTML_Strong($this->getName())), 'content');
    
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

class XSD_Type extends XSD_Container { // complex or simple, but defined
  
  private $aRestrictions = array();
  private $aAttributes = array();
  
  private $oBase = null;
  
  /*
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
  
  public function validate(XSD_Instance $oInstance, $aPath = array()) {
    
    $bResult = false;
    
    if ($this->isSimple()) {
      
      if ($oInstance->getNode()->isComplex()) {
        
        $this->dspm(xt('L\'élément ne devrait pas être de type complex'), 'xml/error');
        
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
              
              $oInstance->addMessage($sMessage, 'content', 'invalid');
              $bResult = $bSubResult;
            }
          }
          
          if (!$bResult) {
            
            if ($bChoices && $this->useMessages())
              $oInstance->addMessage(xt('Cette valeur n\'est pas autorisée'), 'content', 'invalid');
          }
        }
      }
      
    } else {
      
      if (!$oInstance->getParticle()) { // simple type should be complex
        
        if ($this->keepValidate()) $oInstance->buildParticle();
      }
      
      if ($this->isMixed()) $bResult = true; // TODO better validation
      else if (!$this->getParticle()) {
        
        $this->dspm(xt('Impossible de valider l\'élément %s, particule inexistante', view($this->getSource())), 'xml/warning');
        
      } else { // ok, continue
        
        if (!$this->getParser()->useType($this->getName())) { // avoid recursion
          
          $this->getParser()->pushType($this->getName());
          $bResult = $this->getParticle()->validate($oInstance->getParticle(), $aPath);
          $this->getParser()->popType();
        }
      }
    }
    
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
          
        } else if (!$sBase = $oExtend->getAttribute('base')) {
          
          $this->dspm(xt('Aucune base désigné pour l\'extension du composant %s', view($oComponent)), 'xml/error');
          
        } else { // valid
          
          $oType = $this->getParser()->getType($sBase, $oComponent);
          
          if ($bComplexType && $bComplexContent) { // complexContent
            
            // simple extension add to the end
            
            if ($oExtend->getFirst()) {
              
              if (!$oType->getSource()) {
                
                $this->dspm(xt('Le type %s ne peut pas étendre le type %s qui est invalide',
                  view($oComponent), view($oType->getSource())), 'xml/error');
                
              } else if ($oType->getSource()->getFirst()->getName() != $oExtend->getFirst()->getName()) {
                
                $this->dspm(xt('La particule de %s doit être identique à la particule de %s',
                  view($oType->getSource()), view($oExtend)), 'xml/error');
                
              } else {
                
                $oExtend->getFirst()->shift($oType->getSource()->getFirst()->getChildren());
                $this->setParticle(new XSD_Particle($oExtend->getFirst(), $this, $aPath));
              }
            }
            // $mResult = new XML_Element($oComponent->getName(), null, null, $this->getNamespace());
            
            // TODO $mResult->add($oType->getChildren(), $this->buildElement());
            
          } else { // simpleType & simpleContent
            
            $this->oBase = $oType;
            
            if ($oType->hasRestrictions()) { // if not empty type
              
              if ($oExtend->getName() != 'extension') { // restriction
                
                $this->aRestrictions = $oType->getRestrictions();
                $this->buildRestrictions($oExtend);
                
              } else { // extension
                
                // what TODO ?
                $this->dspm('TODO', 'xml/error');
              }
            }
            
            $this->buildRestrictions($oExtend);
          }
          
          //$mResult->add($oExtend);
        }
        
      } else { // complex type definition
        
        // WARNING : no check if valid children, if not group
        if ($oFirst->getName() == 'group') $this->setParticle(new XSD_Group($oFirst, $this));
        else $this->setParticle(new XSD_Particle($oFirst, $this, $aPath));
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
  
  public function getClasses() {
    
    return array();
  }
  
  public function getPath() {
    
    return $this->getName();
  }
  
  public function parse() {
    
    $oResult = new XML_Element('base', null, array('name' => $this), $this->getNamespace());
    
    if (!$this->isSimple()) $oResult->setAttribute('lc:complex', 'true', $this->getNamespace());
    
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



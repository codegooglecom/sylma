<?php

/**
 * Following nodes are currently implemented :
 * - element, complexType, simpleType, attribute, sequence
 * - minInclusive, maxInclusive, length, minLength, maxLength, enumeration, pattern [... TODO]
 * Following has limitations [TODO more details] :
 * - extension, restriction, @mixed
 * 
 * Following simple types are currently supported :
 * - xs:string, xs:date, xs:boolean, xs:integer, xs:decimal
 * Following has no validation but can be used :
 * - xs:dateTime, xs:duration, xs:time
 * 
 * TODO :
 * Following nodes or functions are NOT yet implemented. Hope it will be soon.
 * - group, @ref, choice, all, attributeGroup, union, any, assertion [... TODO]
 * Lot of simple types are NOT yet implemented, see implemented ones above. [... TODO]
 * Sequences, choices cannot be imbricated in each other
 * Some abstract classes share identical methods, those classes should be rebuild to avoid this
 * Instances setStatut(), isValid(), use/addMessages(), (..) methods should be merged in an "invalidation" structure/method
 * Finish comments
 */
class XSD_Parser extends ModuleBase {
  
  const NS = 'http://www.sylma.org/schemas';
  
  private $aOptions = array(
    'model' => false, // if false, it will not build the model
    'messages' => true, // if false, validation and building will stop at first error
    'is-valid' => true, // valid or not
    'mark' => true, // copy sylma schema namespaced attributes to elements
    'load-refs' => false); // load key-refs datas, require database
  
  private $oModel = null; // Result object
  
  private $aMessages = array();
  
  public $aTypes = array(); // list of xs:type's, rebuilded extended or restricted xs:type's and inline complexType)
  public $aTypesUsed = array(); // stack of types to avoid recursive calls - TODO reset to private
  
  private $aGroups = array(); // list of xs:group's
  private $aElements = array(); // list of xs:element's defined in root
  
  private $aRefs = array(); // list of element datas loaded from DB. Indicate in schema with @lc:key-ref
  
  private $iID = 1; // used to increment @id set to model and refering @model set to source node
  
  private $oElement = null;
  
  public function __construct(XML_Document $oSchema, XML_Document $oDatas = null, $aOptions = array()) {
    
    $this->setDirectory(__file__);
    $this->setArguments(Sylma::get('schemas'));
    
    $this->setNamespace(self::NS, 'lc');
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
  
  /**
   * Namespace that should be used in the document to validate
   */
  public function getTargetNamespace() {
    
    return $this->getSchema()->getAttribute('targetNamespace');
  }
  
  /**
   * Generate a random ID number incremented each time
   * @return int
   */
  public function getID() {
    
    return $this->iID++;
  }
  
  /**
   * Main parser function that will build the model from the schema and alterate source nodes with model id attribute.
   * Depends and options.
   *
   * @param XML_Document|null $oDatas Document source to build the model result from.
   *   If null 'model' option should be set to (bool) true to generate entire new document
   */
  
  private function buildSchema(XML_Document $oDatas = null) {
    
    $oResult = null;
    $oRoot = $oDatas ? $oDatas->getRoot() : null;
    
    if ($sPath = $this->getOption('path')) $aPath = explode('/', $sPath);
    else $aPath = array();
    
    if (!$aPath && !$oRoot) $this->throwException(txt('No root defined'));
    
    $sRoot = $aPath ? $aPath[0] : $oRoot->getName();
    
    if (!$this->getSchema()) {
      
      $this->dspm(xt('Cannot validate or build model, no schema defined'), 'warning');
    }
    else if ($oRoot && ($oSource = $this->getSchema()->get("/*/xs:element[@name='".$sRoot."']", $this->getNS()))) {
      
      $oElement = $this->create('element', array($oSource, null, null, $this, $aPath));
      $this->aElements = $oElement;
      
      // if ($sPath) $oElement = $this->getElement();
      
      if (!$oElement) {
        
        $this->dspm(xt('Elément manquant'), 'xml/error');
        
      } else {
        
        $oModel = $oElement->getInstance(null, $oRoot);
        
        $this->isValid($oElement->validate($oModel, $aPath));
        
        if ($this->keepValidate()) {
          
          $oSchemas = new XML_Element('schemas', array(
            $this->aRefs,
            $this->aTypes,
            $this->aGroups,
            $this->aElements,
            $oModel), null, $this->getNamespace());
          
          $oResult = new XML_Element('sylma-schema', array($oDatas, $oSchemas), array(
            'xmlns:html' => SYLMA_NS_XHTML, 'xmlns:lc' => $this->getNamespace()), $this->getNamespace());
        }
      }
    } else {
      
      $this->isValid(false);
      dspf($this->getSchema());
      $this->throwException(txt('Root element %s not found in schema', $sRoot));
      
      foreach ($this->getSchema()->query("/*/xs:element", $this->getNS()) as $oElement) {
        // TODO, no valid root element or not at all
      }
    }
    
    // if (!$this->isValid()) $this->dspm(xt('Des valeurs indiquées dans ce formulaire ne sont pas valides'), 'xml/warning');
    // dspf($oResult);
    // dspf($this->getSchema());
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
  
  public function addType(XSD_Type $oType, $oElement) {
    
    $this->aTypes[$oElement->getNamespace()][$oType->getName()] = $oType;
    
    return $oType;
  }
  
  private function addGroup($oGroup, $oElement) {
    
    $this->aGroups[$oElement->getNamespace()][$oGroup->getName()] = $oGroup;
    
    return $oGroup;
  }
  
  /**
   * Load the file infos parse and add it to refs
   *
   */
  public function addFile(XML_Element $oNode, XML_Element $oElement, XSD_Model $oModel) {
    
    if ($this->getOption('load-refs')) { // replace ref with corresponding nodes
      
      $sPath = $oNode->read();
      
      if (!$oFile = Controler::getFile($sPath)) {
        
        dspm(xt('Aucun fichier ne correspond à %s dans l\'élément %s',
          new HTML_Strong($sPath), new HTML_Strong($oModel->getName())), 'warning');
      }
      else {
        
        $oFile = $oFile->parseXML();
        $sFile = uniqid('file-');
        
        $oFile->setAttribute('lc:id-file', $sFile, $this->getNamespace());
        $oNode->setAttribute('lc:file-ref', $sFile, $this->getNamespace());
        
        $this->aRefs[] = $oFile;
      }
    }
  }
  
  public function addRef($oElement) {
    
    $mRef = $oElement->getAttribute('key-ref', $this->getNamespace());
    
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
        
        $mResult = $this->addGroup($this->create('group', array($oDefElement, $oParent)), $oElement); // new group
      }
      
    } else if ($oElement->hasChildren()) { // anonymous group
      
      $mResult = $this->addGroup($this->create('group', array($oElement, $oParent)), $oElement);
      
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
      
      $mResult = $this->create('simpletype', array($sType, $this));
      
    } else { // Other namespaces datatypes
      
      if (array_key_exists($sNamespace, $this->aTypes) && array_key_exists($sType, $this->aTypes[$sNamespace])) { // ever seen
        
        $mResult = $this->aTypes[$sNamespace][$sType];
        
      } else { // new type
        
        $sTypes = "/*/xs:complexType[@name='$sType'] | /*/xs:simpleType[@name='$sType']";
        if (!$oElement = $this->getSchema()->get($sTypes, $this->getNS())) {
          
          $this->dspm(xt('Type %s introuvable dans %s', new HTML_Strong($sType), view($this->getSchema())), 'xml/error');
          
        } else {
          
          $mResult = $this->create('complextype', array($oElement, null, null, $this, $aPath));
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
    
    if (!$bValid && $bValid !== null) $this->aOptions['is-valid'] = false;
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
  
  public function throwException($sMessage, $mSender = array(), $iOffset = 2) {
    
    return parent::throwException($sMessage, $mSender, $iOffset);
  }
  
  public function parse() {
    
    return $this->oModel;
  }
}


<?php

namespace sylma\dom\basic;
use \sylma\dom, \sylma\storage\fs, \sylma\core;

require_once('core/module/Namespaced.php');

require_once(dirname(__dir__) . '/handler.php');
require_once('core/argumentable.php');
require_once('core/tokenable.php');

/**
 * Existenz of this class mainly due to https://bugs.php.net/bug.php?id=28473
 * Allow too extension of document methods with others arguments
 */
class Handler extends core\module\Namespaced implements dom\handler, core\argumentable, core\tokenable {
  
  /**
   * See @method setControler()
   * @var dom\Controler
   */
  private $controler;
  
  /**
   * See @method setFile()
   * @var fs\file
   */
  private $file;
  
  /**
   *
   * @var dom\document
   */
  private $document;
  
  private $fragment;
  
  private $sContent = '';
  
  /**
   * Namespaces linked to this document also used by nodes
   */
  protected $aNamespaces = array();
  
  protected $aClasses = array();
  
  /**
   * See @method setMode() for details
   */
  private $iMode = null;
  
  private $bFragment;
  
  
  public function __construct($mContent = '', $iMode = \Sylma::MODE_READ, array $aNamespaces = array()) {
    
    $controler = \Sylma::getControler('dom');
    
    $this->setControler($controler);
    $this->setMode($iMode);
    
    $this->setDocument($controler->create('document'));
    
    $this->registerClasses();
    $this->registerNamespaces($aNamespaces);
    
    $this->setFragment($this->document->createDocumentFragment());
    
    if ($mContent) {
      
      if (is_object($mContent)) $this->set($mContent);
      else if (is_string($mContent)) $this->startString($mContent);
    }
  }
  
  public function isEmpty() {
    
    return $this->document->isEmpty();
  }
  
  public function setRoot(dom\element $el) {
    
    $container = $this->getContainer();
    
    return $container->setRoot($el);
  }
  
  public function getRoot() {
    
    $container = $this->getContainer();
    
    return $this->getContainer()->getRoot();
  }
  
  public function getControler() {
    
    return $this->controler;
  }
  
  public function setControler(dom\Controler $controler) {
    
    $this->controler = $controler;
  }
  
  protected function setFragment(dom\fragment $fragment) {
    
    $this->fragment = $fragment;
  }
  
  public function getContainer() {
    
    $result = null;
    
    if ($this->bFragment) $result = $this->getFragment();
    else $result = $this->getDocument();
    
    if (!$result) $this->throwException(t('No valid container defined'));
    
    return $result;
  }
  
  public function getDocument() {
    
    return $this->document;
  }
  
  protected function setDocument(dom\document $doc) {
    
    $doc->setHandler($this);
    $this->document = $doc;
  }
  
  protected function getContent() {
    
    return $this->sContent;
  }
  
  protected function setContent($sContent) {
    
    $this->sContent = $sContent;
  }
  
  private function setMode($iMode) {
    
    $aModes = array(\Sylma::MODE_EXECUTE, \Sylma::MODE_WRITE, \Sylma::MODE_READ);
    
    if (in_array($iMode, $aModes)) $this->iMode = $iMode;
  }
  
  public function getMode() {
    
    return $this->iMode;
  }
  
  public function startString($sValue) {
    
    $bResult = false;
    
    if ($sValue{0} == '/') {
      
      $fs = \Sylma::getControler('fs');
      $file = $fs->getFile($sValue);
      
      $this->setFile($file);
      $bResult = $this->loadFile();
    }
    else if ($sValue{0} == '<') {
      
      $bResult = $this->loadText($sValue);
    }
    else {
      
      $bResult = (bool) $this->set($this->createElement($sValue, '', null, '', $this));
    }
    
    return $bResult;
  }
  
  /**
   * Register some couples prefix => namespaces that will be used in next queries
   *   Used in @method dom\element\get, @method dom\element\query and @method dom\element\read
   * @param array $aNS The couples prefix => namespaces
   */
  public function registerNamespaces(array $aNS = array()) {
    
    $this->aNamespaces = array_merge($this->aNamespaces, $aNS);
  }
  
  /**
  * @return The registered namespaces with @method registerNamespaces() used in xpath element methods
  */ 
  public function getNamespaces() {
    
    return $this->aNamespaces;
  }
  
  /**
   * Set the used class for returned child nodes
   * @param core\argument $settings The classes to use for child node
   */
  public function registerClasses(core\argument $settings = null) {
    
    $aClasses = $this->getControler()->getClasses($settings);
    
    foreach ($aClasses as $sOrigin => $sReplacement) {
      
      $this->document->registerNodeClass($sOrigin, $sReplacement);
    }
  }
  
  public function addElement($sName, $mContent = '', array $aAttributes = null, $sNamespace = null) {
    
    $result = null;
    $el = $this->createElement($sName, $mContent, $aAttributes, $sNamespace);
    
    if (!$this->getRoot()) {
      
      $result = $this->setRoot($el);
    }
    else {
      
      $result = $this->getRoot()->insertChild($el);
    }
    
    return $result;
  }
  
  public function createElement($sName, $mContent = '', array $aAttributes = array(), $sNamespace = null) {
    
    $doc = $this->getDocument();
    
    if (!$sName) $this->throwException(t('Empty value cannot be used as element\'s name'));
    
    if ($sNamespace) {
      
      $el = $doc->createElementNS($sNamespace, $sName);
    }
    else {
      
      $el = $doc->createElement($sName);
    }
    
    if ($mContent) {
      
      $el->set($mContent);
    }
    
    if ($aAttributes) {
      
      $el->setAttributes($aAttributes);
    }
    
    return $el;
  }
  
  public function setFile(fs\file $file) {
    
    $this->file = $file;
  }
  
  public function getFile() {
    
    return $this->file;
  }
  
  public function loadFile() {
    
    $bResult = false;
    
    if (!$this->getFile()) {
      
      $this->throwException(t('No file associated'));
    }
    
    return $this->parseContent($this->getFile()->read());
  }
  
  public function loadText($sContent, $bLoad = true) {
    
    $bResult = false;
    $this->setContent($sContent);
    
    if ($bLoad) $bResult = $this->loadContent();
    
    return $bResult;
  }
  
  protected function loadContent() {
    
    return $this->parseContent($this->getContent());
  }
  
  protected function parseContent($sContent) {
    
    $reader = new \XMLReader;
    $reader->XML($sContent);
    
    $aNS = $this->lookupNamespaces($reader);
    $this->registerNamespaces($aNS);
    
    return $this->document->loadXML($sContent);
  }
  
  private function lookupNamespaces(\XMLReader $reader) {
    
    $aNS = array();
    
    while ($reader->read()) {
      
      switch ($reader->nodeType) {
        
        // case \XMLReader::NONE : break;
        case \XMLReader::ELEMENT :
          
          $aNS[$reader->namespaceURI] = true;
          
          if($reader->hasAttributes) {
            
            while($reader->moveToNextAttribute()) {
              
              $aNS[$reader->namespaceURI] = true;
            }
          }
          
          if (!$reader->isEmptyElement) {
            
            $aNS = array_merge($aNS, $this->lookupNamespaces($reader));
          }
          
        break;
        // case \XMLReader::ATTRIBUTE : break;
        // case \XMLReader::TEXT : break;
        case \XMLReader::END_ELEMENT : //dspf($reader->expand(new \XML_Element)); break 2;
        // case \XMLReader::XML_DECLARATION : break;
      }
    }
    
    return $aNS;
  }
  
  protected function checkRoot() {
    
    if (!$root = $this->getRoot()) {
      
      $this->throwException(t('No root element defined'));
    }
    
    return $root;
  }
  
  // From here : various methods for direct call to root, instead of __call magical method
  
  public function get($sQuery, array $aNS = array()) {
    
    if (!$root = $this->getRoot()) {
      
      $this->throwException(t('No root element defined'));
    }
    else {
      
      $aNS = array_merge($this->getNamespaces(), $aNS);
      
      $result = $root->get($sQuery, $aNS);
    }
    
    return $result;
  }
  
  public function read($sQuery = '', array $aNS = array()) {
    
    $sResult = '';
    
    if (!$root = $this->getRoot()) {
      
      $this->throwException(t('No root element defined'));
    }
    else {
      
      $sResult = $root->read($sPath);
    }
    
    return $sResult;
  }
  
  public function query($sQuery = '', array $aNS = array(), $bConvert = true) {
    
    $root = $this->checkRoot();
    return $root->query($sQuery, $aNS, $bConvert);
  }
  
  protected function setObject($val) {
    
    $result = null;
    
    if ($val instanceof dom\element || $val instanceof dom\fragment) {
      
      $result = $this->setRoot($val);
    }
    else if ($val instanceof dom\document) {
      
      if ($val->isEmpty()) {
        
        $this->throwException(t('Empty document cannot be setted to another document'));
      }
      
      $result = $this->setRoot($val->getRoot());
    }
    else if ($val instanceof dom\collection) {
      
      $val->rewind();
      $this->set($val->current());
      $val->next();
      
      while ($val->valid()) {
        
        $this->add($val->current());
        $val->next();
      }
    }
    else if ($val instanceof core\argumentable) {
      
      $result = $this->setArgument($val->asArgument());
    }
    else if ($val instanceof dom\domable) {
      
      $result = $this->setRoot($val->asDOM());
    }
    else if ($val instanceof \DOMDocument) {
      
      $el = $val->documentElement;
      $result = $this->setDOMNode($el);
    }
    else if ($val instanceof \DOMNode) {
      
      $result = $this->setDOMNode($val);
    }
    else {
      
      $formater = \Sylma::getControler('formater');
      $this->throwException(txt('Object %s cannot be used in dom document', $formater->asToken($val)));
    }
    
    return $result;
  }
  
  protected function setDOMNode(\DOMNode $node) {
    
    $container = $this->getContainer();
    
    $el = $container->importNode($node);
    $this->setRoot($el);
  }
  
  protected function setArgument(core\argument $arg) {
    
    $doc = $arg->getDocument();
    
    return $this->setObject($doc);
  }
  
  protected function setArray($aVal) {
    
    $mResult = array();
    
    if (count($aVal) > 1) {
      
      // > 1
      
      $aChildren = array();
      
      $this->set(array_shift($aVal));
      foreach ($aVal as $oChild) $aChildren = $this->add($oChild);
      
      $mResult = $aChildren;
      
    } else {
      
      // = 1
      
      $mResult = $this->set(array_pop($mValue));
    }
    
    return $mResult;
  }
  
  public function set() {
    
    $mResult = null;
    
    if (!func_num_args()) {
      
      $this->getRoot()->remove();
    }
    else if (func_num_args() == 1) {
      
      $mValue = func_get_arg(0);
      
      if (is_object($mValue)) {
        
        $mResult = $this->setObject($mValue);
      }
      else if (is_array($mValue) && $mValue) {
        
        $mResult = $this->setArray($mValue);
      }
      else if (is_string($mValue)) {
        
        $mResult = $this->startString($mValue);
      }
    }
    else if (func_num_args() > 1) {
      
      $this->set(func_get_args());
    }
    
    return $mResult;
  }
  
  public function add() {
    
    $root = $this->checkRoot();
    return $root->add(func_get_args());
  }
  
  public function getChildren() {
    
    $root = $this->checkRoot();
    return $root->getChildren(func_get_args());
  }
  
  public function hasChildren() {
    
    $root = $this->checkRoot();
    return $root->hasChildren();
  }
  
  public function getFirst() {
    
    $root = $this->checkRoot();
    return $root->getFirst();
  }
  
  public function asToken() {
    
    if ($this->getFile()) $sResult = '@file ' . $this->getFile();
    else $sResult = '@file [unknown]';
    
    return $sResult;
  }
  
  public function asArgument() {
    
    $dom = $this->getControler();
    $content = null;
    
    if (!$this->isEmpty()) {
      
      // copy handler for display updates (add of whitespaces)
      $copy = $dom->create('handler', array($this));
      $copy->getRoot()->prepareHTML();
      
      $content = $copy->getContainer()->saveXML(null);
    }
    
    return $dom->createArgument(array(
        'handler' => array(
            '@class' => get_class($this),
            'content' => $this->getDocument(),
        ),
    ), $dom->getNamespace());
  }
  
  public function asString(dom\node $el = null) {
    
    if (!$sResult = $this->getContent()) {

      $doc = $this->getContainer();
      
      if ($el) $sResult = $doc->saveXML($el);
      else $sResult = $doc->saveXML();
    }
    
    return $sResult;
  }
  // public function add()
  // public function set()
  public function throwException($sMessage, $mSender = array(), $iOffset = 2) {
    
    $mSender = (array) $mSender;
    
    $mSender[] = '@namespace ' . self::NS;
    $mSender[] = $this->asToken();
    
    $dom = $this->getControler();
    \Sylma::throwException($sMessage, $mSender, $iOffset);
  }
  
  public function __toString() {
    
    $sResult = '';
    
    try {
      
      $sResult = $this->asString();
    }
    catch (\Exception $e) {
      
      \Sylma::log($this->asToken(), $e->getMessage());
    }
    
    return $sResult;
  }
}
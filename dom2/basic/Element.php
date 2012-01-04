<?php

namespace sylma\dom\basic;
use \sylma\dom, \sylma\core;

require_once(dirname(__dir__) . '/element.php');
require_once('core/tokenable.php');

class Element extends \DOMElement implements dom\element, core\tokenable {
  
  const CONTROLER_ALIAS = 'dom';
  
  // public function __construct()
  
  public function getDocument() {
    
    return $this->ownerDocument;
  }
  
  public function getHandler() {
    
    if (!$doc = $this->getDocument()) $this->throwException(t('No document defined'));
    $handler = $doc->getHandler();
    
    return $handler;
  }
  
  protected function getControler() {
    
    return \Sylma::getControler(self::CONTROLER_ALIAS);
  }
  
  /**
   * Create a DOMXPath object
   * @return DOMXPath An XPath associated with querie's namespaces
   */
  private function buildXPath(array $aNS = array()) {
    
    $xpath = new \DOMXPath($this->getDocument());
    foreach ($aNS as $sPrefix => $sNamespace) $xpath->registerNamespace($sPrefix, $sNamespace);
    
    return $xpath;
  }
  
  public function getNamespaces() {
    
    $aResult = array();
    
    $handler = $this->getHandler();
    
    return $handler->getNamespaces();
  }
  
  public function read($sQuery = '', array $aNS = array()) {
    
    $sResult = '';
    
    if ($sQuery) {
      
      $aNS = array_merge($aNS, $this->getNamespaces());
      
      $xpath = $this->buildXPath($aNS);
      $mResult = $xpath->evaluate($sQuery, $this);
      
      $this->getControler()->addStat('evaluation', array($sQuery, $aNS));
      
      if ($mResult instanceof \DOMNodeList) {
        
        // node list result
        foreach ($mResult as $node) {
          
          $sResult .= (string) $node;
        }
      }
      else {
        
        // string result
        $sResult = (string) $mResult;
      }
      
    } else {
      
      $sResult = $this->nodeValue;
    }
    
    return $sResult;
  }
  
  public function query($sQuery = '', array $aNS = array(), $bConvert = true) {
    
    if ($bConvert) $result = $this->getControler()->create('collection');
    else $result = null;
    
    if ($sQuery) {
      
      $aNS = array_merge($aNS, $this->getNamespaces());
      
      $xpath = $this->buildXPath($aNS);
      $domlist = $xpath->query($sQuery, $this);
      
      $this->getControler()->addStat('query', array($sQuery, $aNS));
      
      if ($bConvert) $result->addArray($domlist);
      else $result = $domlist;
    }
    else {
      
      $result = $this->getChildren();
    }
    
    return $result;
  }
  
  public function get($sQuery, array $aNS = array()) {
    
    $collection = $this->query($sQuery, $aNS, false);
    
    if ($collection && $collection->length) $result = $collection->item(0);
    else $result = null;
    
    return $result;
  }
  
  public function readByName($sName, $sNamespace = null) {
    
    if ($el = $this->getByName($sName, $sNamespace)) $sResult = $el->read();
    else $sResult = '';
    
    return $sResult;
  }
  
  public function queryByName($sName, $sNamespace = null) {
    
    if ($sNamespace) $aElements = $this->getElementsByTagNameNS($sNamespace, $sName);
    else $aElements = $this->getElementsByTagName($sName);
    
    $result = $this->getControler()->create('collection', array($aElements));
    
    return $result;
  }
  
  public function getByName($sName, $sNamespace = null) {
    
    $aElements = $this->queryByName($sName, $sNamespace);
    
    if ($aElements->length) $result = $aElements->item(0);
    else $result = null;
    
    return $result;
  }
  
  public function getAttributes() {
    
    $result = $this->getControler()->create('collection', array($this->attributes));
    
    return $result;
  }
  
  public function setAttributes(array $aAttributes = array()) {
    
    if ($aAttributes) {
      
      foreach ($aAttributes as $sName => $sValue) {
        
        $this->setAttribute($sName, $sValue);
      }
    }
    else {
      
      foreach ($this->getAttributes() as $attr) $attr->remove();
    }
  }
  
  public function getParent() {
    
    return $this->parentNode;
  }
  
  public function isRoot() {
    
    return (!$this->getParent() || ($this->getParent() === $this->getDocument()));
  }
  
  // public function getById($sId)
  // public function testAttribute($sName, $sUri = '')
  // public function setAttribute($sName, $sValue = '', $sUri = null)
  // public function setAttributes($mAttributes)
  // public function getAttributes()
  // public function getAttribute($sName, $sUri = '')
  // public function shift()
  // public function insert($mValue, $oNext = null)
  
  // *private function insertText($sValue, $oNext = null)
  // *private function insertChild($oChild, $oReferer = null, $bPrevious = false)
  
  // public function insertBefore()
  // public function insertAfter()
  /**
   * Insert the string variable result given in argument before the $oNext element, if null insert at the end of the children's list
   * @param mixed $sValue The value to add to actual content, will be transform to text
   * @param XML_Element $oNext The element that will follow the value
   * @return XML_Text The text element added to content
   */
  public function insertText($sValue, dom\node $next = null) {
    
    $node = $this->getDocument()->createTextNode($sValue);
    
    if ($sValue !== null && $sValue !== '') return $this->insertChild($node, $next);
    else return $sValue;
  }
  
  /**
   * Insert the element given in argument before the $oNext element, if null insert at the end of the children's list
   * @param XML_Element $oChild The element to add to actual content
   * @param XML_Element $oNext The element that will follow the value
   * @return XML_Element The element added to content
   */
  public function insertChild(\DOMNode $node, dom\node $referer = null, $bPrevious = false) {
    
    $result = null;
    
    if ($node === $referer) $referer = null;
    
    if ($node->ownerDocument && ($node->ownerDocument !== $this->getDocument())) {
      
      $node = $this->getDocument()->importNode($node);
    }
    
    if ($bPrevious) {
      
      if ($referer && $referer->getNext()) $result = parent::insertBefore($node, $referer->getNext());
      else if ($referer) $result = parent::appendChild($node);
      else $result = parent::insertBefore($node, $this->getFirst());
    }
    else {
      
      if ($referer) $result = parent::insertBefore($node, $referer);
      else $result = parent::appendChild($node);
    }
    
    return $result;
  }
  
  // public function addElement($sName, $oContent = '', $aAttributes = null, $sUri = null)
  // public function createElement($sName, $oContent = '', $aAttributes = null, $sUri = null)
  // *public function insertNode($sName, $oContent = '', $aAttributes = null, $sUri = null, $oNext = null, $bPrevious = false)
  
  protected function insertObject($value, dom\node $next = null) {
    
    $mResult = null;
    
    if ($value instanceof dom\fragment) {
      
      $mResult = $this->insertChild($value); // TODO
    }
    else if ($value instanceof dom\attribute) {
      
      $this->setAttributeNode($value);
    }
    else if ($value instanceof dom\collection) {
      
      foreach ($value as $oChild) $this->insert($oChild, $next);
    }
    else if ($value instanceof dom\document) {
      
      if ($value->getRoot()) $mResult = $this->insertChild($value->getRoot(), $next);
      else $mResult = null;
    }
    else if ($value instanceof dom\node) {
      
      // element, text, cdata, comment
      
      $mResult = $this->insertChild($value, $next);
    }
    else if ($value instanceof dom\domable) {
      
      $node = $value->asDOM();
      $mResult = $this->insert($node, $next);
    }
    else if ($value instanceof core\argumentable) {
      
      $arg = $value->asArgument();
      $mResult = $this->insertArgument($arg, $next);
    }
    else if ($value instanceof \DOMDocument || $value instanceof \DOMElement) {
      
      $mResult = $this->insertChild($value, $next);
    }
    else {
      
      $mResult = $this->insertText((string) $value, $next); // Forced string
    }
    
    return $mResult;
  }
  
  protected function insertArray(array $aValue, dom\node $next = null) {
    
    if (!$aValue) {
      
      $this->throwException(t('Cannot insert empty array'));
    }
    
    $mResult = array();
    
    foreach ($aValue as $mSubValue) $mResult[] = $this->insert($mSubValue, $next);
    
    if ($mResult && count($mResult) == 1) $mValue = array_pop($mResult);
    else $mValue = $mResult;
    
    return $mResult;
  }
  
  protected function insertArgument(core\argument $arg, dom\node $next = null) {
    
    return $this->insert($arg, $next);
  }
  
  public function insert($mValue, dom\node $next = null) {
    
    $mResult = null;
    
    if (is_object($mValue)) {
      
      $mResult = $this->insertObject($mValue, $next);
      
    } else if (is_array($mValue)) {
      
      $mResult = $this->insertArray($mValue, $next);
      
    } else if ($mValue !== null) {
      
      /* String, Integer, Float, Bool, Resource, ... ? */
      
      $mResult = $this->insertText((string) $mValue, $next);
    }
    
    return $mResult;
  }
  
  public function getChildren() {
    
    $result = $this->getControler()->create('collection', array($this->childNodes));
    
    return $result;
  }
  // public function getChildren($sNamespace = null, $iDepth = null, $bCleanComments = false)
  // public function countChildren()
  
  public function hasChildren() {
    
    return $this->hasChildNodes();
  }
  
  public function set() {
    
    $mResult = null;
    
    if (func_num_args() > 1) {
      
      $this->set(func_get_arg(0));
      $mResult = array();
      
      for ($i = 1; $i < func_num_args(); $i++) $mResult[] = $this->add(func_get_arg($i));
      
    } else if (func_num_args() == 1) {
      
      $mValue = func_get_arg(0);
      
      $this->set();
      $mResult = $this->add($mValue);
    }
    else {
      
      if ($this->hasChildren()) $this->getChildren()->remove();
    }
    
    return $mResult;
  }
  
  public function add() {
    
    return $this->insert(func_get_args());
  }
  
  public function addElement($sName, $mContent = '', array $aAttributes = array(), $sNamespace = null) {
    
    $handler = $this->getHandler();
    
    if (!$sNamespace) $sNamespace = $this->getNamespace();
    //echo $sName.'<br/>';
    $el = $handler->createElement($sName, $mContent, $aAttributes, $sNamespace);
    $el = $this->insertChild($el);
    
    return $el;
  }
  // *public function isFirst()
  // *public function isLast()
  // public function isRoot()
  // public function isEmpty()
  // public function isComplex()
  // public function isSimple()
  
  // public function getParent($sNamespace = null) {
    
    // if ($sNamespace) {
      
      // if ($this->isRoot()) return null;
      // else if ($this->getParent()->getNamespace() != $sNamespace) return $this->getParent()->getParent($sNamespace);
      // else return $this->getParent();
      
    // } else return $this->parentNode;
  // }
  // public function getLast() {
    
    // return $this->lastChild;
  // }
  
  /**
   * Remove the actual element
   * @return mixed Don't know what :( TODO
   */
  public function remove() {
    
    $mResult = null;
    
    if ($this->parentNode) $mResult = $this->parentNode->removeChild($this);
    
    return $mResult;
  }
  
  /**
   * Replace the actual element with the one given in argument
   * @param XML_Element $oChild The element wish will replace the actual one
   * @return XML_Element The element added to content
   */
  public function replace($mContent) {
    
    $result = null;
    
    if ($mContent !== $this) {
      
      if ($this->isRoot()) {
        
        $result = $this->getDocument()->set($mContent);
        
      } else {
        
        $result = $this->getParent()->insert($mContent, $this);
        $this->remove();
      }
    }
    
    return $result;
  }

  public function getFirst() {
    
    return $this->firstChild;
  }
  
  // public function getNext() {
    
    // return $this->nextSibling;
  // }
  // public function getPrevious() {
    
    // return $this->previousSibling;
  // }
  
  public function getNamespace($sPrefix = '') {
    
    if ($sPrefix !== '') return $this->lookupNamespaceURI($sPrefix);
    else return $this->namespaceURI;
  }
  
  public function getPrefix() {
    
    return $this->prefix;
  }
  
  public function getName($bLocal = true) {
    
    if ($bLocal) return $this->localName;
    else return $this->nodeName;
  }
  
  // public function move($oElement)
  
  // public function pushAttribute($sName, $sValue, $sNamespace = null)
  // *public function useNamespace($sNamespace = null)
  // *public function hasAttribute
  // *public function cloneAttributes($oElement, $mAttribute = null, $sNamespace = null)
  // public function cleanChildren()
  // public function cleanAttributes()
  
  // public function getCSSPath
  // public function merge($oElement, $bSelfPrior = false)
  // public function toArray($sAttribute = null, $iDepthAttribute = 0)
  // public function extractNS($sNamespace, $bKeep = false)
  // private static function compareAttributes($el1, $el2)
  // public function compare(NodeInterface $element, $aPath = array())
  // public function updateNamespaces($mFrom = null, $mTo = null, $mPrefix = '', $oParent = null)
  
  public function getPath() {
    
    $sResult = '';
    
    $sLine = '';
    if (method_exists($this, 'getLineNo')) $sLine = $this->getLineNo();
    
    if (!$sLine) $sLine = 'xx';
    $sResult .= $this->getNamespace() . ':' . $this->getName(true);
    
    // if @id or @name, display it
    
    if ($sID = $this->getAttribute('id')) $sResult .= '[@id = ' . $sID . ']';
    else if ($sName = $this->getAttribute('name')) $sResult .= '[@name = ' . $sName . ']';
    
    $sResult .=  ' (line ' . $sLine . ')';
    
    return $sResult;
  }
  
  public function asToken() {
    
    return '@element ' . $this->getPath();
  }
  
  public function prepareHTML($iLevel = 0) {
    
    if (!$this->isRoot()) $this->insert("\n".str_repeat('  ', $iLevel), $this->getFirst());
    
    foreach ($this->getChildren() as $child) {
      
      if ($child instanceof dom\element) {
        
        $child->prepareHTML($iLevel + 1);
        
      } else if ($child instanceof dom\cdata || $child instanceof dom\comment) {
        
        $child->getParent()->insert("\n".str_repeat('  ', $iLevel + 1), $child);
      }
    }
    
    if ($this->hasChildren()) {
      
      if ($this->getChildren()->length > 1) $this->add("\n".str_repeat('  ', $iLevel)); // || strlen($this->getFirst()) > 80
    }
  }
  
  protected function throwException($sMessage) {
    
    $aPath = array($this->asToken());
    
    if (!$controler = $this->getHandler()) {
      
      $controler = $this->getControler();
    }
    
    $controler->throwException($sMessage, $aPath);
  }
  
  public function __toString() {
    
    return $this->nodeValue;
  }
}
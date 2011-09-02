<?php

namespace sylma\dom\basic;
use \sylma\dom;

require_once('dom2/element.php');

class Element extends \DOMElement implements dom\element {
  
  // public function __construct()
  
  /**
   * @return dom\document The parent document
   */
  public function getDocument() {
    
    return $this->ownerDocument;
  }
  
  public function getControler() {
    
    return $this->getDocument()->getControler();
  }
  
  // public function getPath()
  
  /**
   * Create a DOMXPath object
   * @return DOMXPath An XPath associated with querie's namespaces
   */
  private function buildXPath(array $aNS = array()) {
    
    $xpath = new \DOMXPath($this->getDocument());
    foreach ($aNS as $sPrefix => $sNamespace) $xpath->registerNamespace($sPrefix, $sNamespace);
    
    return $xpath;
  }
  
  /**
   * Evaluation of an xpath expression with text returned
   * 
   * @param string $sQuery The query to evaluate
   * @param array $aNS Prefixes as keys and related namespaces
   * 
   * @return string The result of the evaluated expression
   */
  public function read($sQuery = '', array $aNS = array()) {
    
    $sResult = '';
    
    if ($sQuery) {
      
      $xpath = $this->buildXPath($aNS);
      
      $mResult = $xpath->evaluate($sQuery, $this);
      $this->getControler()->addStat('evaluation', array($sQuery, $aNS));
      
      if ($mResult instanceof \DOMNodeList) {
        
        foreach ($mResult as $node) {
          
          $sResult .= (string) $node;
        }
      }
      else {
        
        $sResult = (string) $mResult;
      }
      
    } else {
      
      $sResult = $this->nodeValue;
    }
    
    return $sResult;
  }
  
  /**
   * Evaluation of an xpath expression with a list returned
   * 
   * @param string $sQuery Query to evaluate
   * @param array $aNS Prefixes as keys and related namespaces
   * 
   * @return dom\collection|\DOMNodeList The result of the evaluated expression
   */
  public function query($sQuery, $aNS, $bConvert = true) {
    
    if ($bConvert) $result = $this->getControler()->create('collection');
    else $result = null;
    
    if ($sQuery) {
      
      $xpath = $this->buildXPath($aNS);
      
      $domlist = $xpath->query($sQuery, $this);
      $this->getControler()->addStat('query', array($sQuery, $aNS));
      
      if ($bConvert) $result->addArray($domlist);
      else $result = $domlist;
    }
    
    return $result;
  }
  
  /**
   * Evaluation of an xpath expression with an element returned
   * 
   * @param string $sQuery Query to evaluate
   * @param array $aNS Prefixes as keys and related namespaces
   * 
   * @return dom\element The first element resulting from the XPath query
   */
  public function get($sQuery, array $aNS = array()) {
    
    $result = null;
    
    $collection = $this->query($sQuery, $aNS, false);
    if ($collection && $collection->length) $result = $collection->item(0);
    
    return $result;
  }
  
  public function readByName($sName, $sUri = null) {
    
    if ($el = $this->getByName($sName, $sUri)) return $el->read();
    else return '';
  }
  
  public function queryByName($sName, $sUri = null) {
    
    if ($sUri) $aResults = $this->getElementsByTagNameNS($sUri, $sName);
    else $aResults = $this->getElementsByTagName($sName);
    
    return $aResults;
  }
  
  public function getByName($sName, $sUri = null) {
    
    $aResults = $this->queryByName($sName, $sUri);
    
    if ($aResults->length) return $aResults->item(0);
    else return null;
  }
  
  // public function getById($sId)
  // public function testAttribute($sName, $sUri = '')
  // public function setAttribute($sName, $sValue = '', $sUri = null)
  // public function setAttributes($mAttributes)
  // public function getAttributes()
  // public function getAttribute($sName, $sUri = '')
  // public function set()
  // public function add()
  // public function shift()
  // public function insert($mValue, $oNext = null)
  
  // *private function insertText($sValue, $oNext = null)
  // *private function insertChild($oChild, $oReferer = null, $bPrevious = false)
  
  // public function insertBefore()
  // public function insertAfter()
  // public function replace($mContent)
  // public function remove()
  
  // public function addElement($sName, $oContent = '', $aAttributes = null, $sUri = null)
  // public function createElement($sName, $oContent = '', $aAttributes = null, $sUri = null)
  // *public function insertNode($sName, $oContent = '', $aAttributes = null, $sUri = null, $oNext = null, $bPrevious = false)
  
  // public function getChildren($sNamespace = null, $iDepth = null, $bCleanComments = false)
  // public function countChildren()
  // *public function hasChildren()
  
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
  // public function getFirst() {
    
    // return $this->firstChild;
  // }
  // public function getNext() {
    
    // return $this->nextSibling;
  // }
  // public function getPrevious() {
    
    // return $this->previousSibling;
  // }
  // public function getNamespace($sPrefix = '') {
    
    // if ($sPrefix !== '') return $this->lookupNamespaceURI($sPrefix);
    // else return $this->namespaceURI;
  // }
  // public function getPrefix() {
    
    // return $this->prefix;
  // }
  // public function getName($bLocal = true) {
    
    // if ($bLocal) return $this->localName;
    // else return $this->nodeName;
  // }
  
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
  
  public function __toString() {
    
    return $this->nodeValue;
  }
}
<?php

namespace sylma\dom\basic;
use sylma\core, sylma\dom;

class Element extends \DOMElement implements dom\element {

  const CONTROLER_ALIAS = 'dom';

  public $compareBadNode;
  // public function __construct()

  public function getDocument() {

    try {

      $doc = $this->ownerDocument;

    } catch (core\exception $e) {

      //$e->save(false);
      //\Sylma::throwException('Lost DOM Document');
      $doc = null;
    }

    return $doc;
  }

  public function getHandler() {

    $handler = $this->getDocument()->getHandler();

    return $handler;
  }

  protected function getControler() {

    return \Sylma::getControler(self::CONTROLER_ALIAS);
  }

  public function getType() {

    return $this->nodeType;
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

  public function mergeNamespaces(array $aNamespaces) {

    $handler = $this->getHandler();

    return $handler->mergeNamespaces($aNamespaces);
  }

  public function read() {

    return $this->nodeValue;
  }

  public function readx($sQuery = '', array $aNS = array(), $bDebug = true) {

    $sResult = '';

    if ($sQuery) {

      $aNS = $this->mergeNamespaces($aNS);

      $xpath = $this->buildXPath($aNS);

      try {

        $mResult = $xpath->evaluate($sQuery, $this);
      }
      catch (core\exception $e) {

        $this->catchError($e, $sQuery);
      }

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

      $sResult = $this->read();
    }

    if (!$sResult && $bDebug) {

      $this->throwException(sprintf('No result for read expression : %s', $sQuery));
    }

    return $sResult;
  }

  protected function catchError(core\exception $e, $sQuery) {

    $aSender = array();
    foreach ($this->getHandler()->mergeNamespaces() as $sPrefix => $sNamespace) $aSender[] = $sPrefix . ' => ' . $sNamespace;

    $this->throwException(sprintf('XPath error with "%s" : %s', $sQuery, $e->getMessage()), $aSender);
  }

  public function queryx($sQuery = '', array $aNS = array(), $bDebug = true, $bConvert = true) {

    if ($bConvert) $result = $this->getControler()->create('collection');
    else $result = null;

    if ($sQuery) {

      $aNS = $this->mergeNamespaces($aNS);
      $xpath = $this->buildXPath($aNS);

      try {

        $domlist = $xpath->query($sQuery, $this);
      }
      catch (core\exception $e) {

        $this->catchError($e, $sQuery);
      }

      $this->getControler()->addStat('query', array($sQuery, $aNS));

      if ($bDebug && !$domlist->length) {

        $this->throwException(sprintf('No result for xpath expression : %s', $sQuery));
      }

      if ($bConvert) $result->addList($domlist);
      else $result = $domlist;
    }
    else {

      $result = $this->getChildren();
    }

    return $result;
  }

  public function getx($sQuery, array $aNS = array(), $bDebug = true) {

    $collection = $this->queryx($sQuery, $aNS, $bDebug, false);

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

  public function setAttribute($sName, $sValue) {

    if ($sValue !== '') {

      $result = parent::setAttribute($sName, $sValue);
    }
    else {

      $result = null;
    }

    return $result;
  }

  public function getParent() {

    return $this->parentNode;
  }

  public function isRoot() {

    return (!$this->getParent() || ($this->getParent() === $this->getDocument()));
  }

  public function readAttribute($sName, $sNamespace = '', $bDebug = true) {

    if ($sNamespace) $sResult = $this->getAttributeNS($sNamespace, $sName);
    else $sResult = $this->getAttribute($sName);

    if ($sResult === '' && $bDebug) {

      $this->throwException(sprintf('No result for @attribute %s:%s', $sNamespace, $sName));
    }

    return $sResult;
  }

  public function testAttribute($sAttribute, $mDefault = null, $sNamespace = '') {

    $bResult = false;

    if (is_string($mDefault)) $bResult = ($this->readAttribute($sAttribute, $sNamespace, false) == $mDefault);
    else $bResult = $this->getControler()->stringToBool(($this->readAttribute($sAttribute, $sNamespace, false)), $mDefault);

    return $bResult;
  }

  public function loadAttribute($sName, $sNamespace = '', $bDebug = true) {

    if ($sNamespace) $result = parent::getAttributeNodeNS($sNamespace, $sName);
    else $result = parent::getAttributeNode($sName);

    if (!$result && $bDebug) {

      $this->throwException(sprintf('No result for @attribute %s:%s', $sNamespace, $sName));
    }

    return $result;
  }

  public function createAttribute($sName, $sValue, $sNamespace = null) {

    if ($sNamespace) $this->setAttributeNS($sNamespace, $sName, $sValue);
    else $this->setAttribute($sName, $sValue);
  }

  public function addToken($sAttribute, $sValue, $sNamespace = null, $sSeparator = ' ') {

    $attr = $this->loadAttribute($sAttribute, $sNamespace, false);

    if ($attr) {

      $aTokens = explode($sSeparator, $attr->getValue());

      if (!in_array($sValue, $aTokens)) {

        $aTokens[] = $sValue;
        $attr->setValue(implode($sSeparator, $aTokens));
      }
    }
    else {

      $this->createAttribute($sAttribute, $sValue, $sNamespace);
    }

    return $this->readAttribute($sAttribute, $sNamespace);
  }

  public function removeToken($sAttribute, $sValue, $sNamespace = null, $sSeparator = ' ') {

    $attr = $this->loadAttribute($sAttribute, $sNamespace);

    if ($attr) {

      $aTokens = explode($sSeparator, $attr->getValue());

      if ($sKey = array_search($sValue, $aTokens)) {

        unset($aTokens[$sKey]);;
        $attr->setValue(implode($sSeparator, $aTokens));
      }
    }

    return $this->readAttribute($sAttribute, $sNamespace, false);
  }

  public function shift() {

    return $this->insert(func_get_args(), $this->getFirst());
  }

  // public function getById($sId)
  // public function setAttribute($sName, $sValue = '', $sUri = null)
  // public function setAttributes($mAttributes)
  // public function getAttributes()
  // public function getAttribute($sName, $sUri = '')
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

    if ($node === $referer) $referer = null;

    if ($node->ownerDocument && ($node->ownerDocument !== $this->getDocument())) {

      $node = $this->getDocument()->importNode($node);
    }

    $result = $node;

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

  public function insertAttribute(\DOMAttr $node) {

    $result = null;

    if ($node->ownerDocument && ($node->ownerDocument !== $this->getDocument())) {

      $node = $this->getDocument()->importNode($node);
    }

    return $this->setAttributeNode($node);
  }

  // public function addElement($sName, $oContent = '', $aAttributes = null, $sUri = null)
  // public function createElement($sName, $oContent = '', $aAttributes = null, $sUri = null)
  // *public function insertNode($sName, $oContent = '', $aAttributes = null, $sUri = null, $oNext = null, $bPrevious = false)

  protected function insertObject($value, dom\node $next = null) {

    $mResult = null;

    if ($value instanceof dom\node) {

      if ($value instanceof dom\fragment) {

        $mResult = $this->insertChild($value); // TODO
      }
      else if ($value instanceof dom\attribute) {

        $mResult = $this->insertAttribute($value);
      }
      else if ($value instanceof dom\collection) {

        foreach ($value as $oChild) {

          $this->insert($oChild, $next);
        }
      }
      else if ($value instanceof dom\document) {

        if ($value->getRoot()) $mResult = $this->insertChild($value->getRoot(), $next);
        else $mResult = null;
      }
      else {

        // element, text, cdata, comment

        $mResult = $this->insertChild($value, $next);
      }
    }
    else if ($value instanceof dom\collection) {

      $mResult = array();

      foreach($value as $sub) {

        $mResult[] = $this->insertChild($sub, $next);
      }

      if ($mResult && count($mResult) == 1) $mResult = current($mResult);
    }
    else if ($value instanceof dom\domable) {

      $node = $value->asDOM();
      $mResult = $this->insert($node, $next);
    }
    else if ($value instanceof core\argumentable) {

      $arg = $value->asArgument();
      $mResult = $this->insertArgument($arg, $next);
    }
    else if ($value instanceof \DOMDocument || $value instanceof \DOMElement || $value instanceof \DOMCdataSection) {

      $mResult = $this->insertChild($value, $next);
    }
    else {

      $mResult = $this->insertText((string) $value, $next); // Forced string
    }

    return $mResult;
  }

  protected function insertArray(array $aValue, dom\node $next = null) {

    if (!$aValue) {

      $this->throwException('Cannot insert empty array');
    }

    $mResult = array();

    foreach ($aValue as $mSubValue) {

      $mResult[] = $this->insert($mSubValue, $next);
    }

    if ($mResult && count($mResult) == 1) $mResult = current($mResult);

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

    //$result = $this->getControler()->create('collection', array($this->childNodes));

    //return $result;
    return $this->getControler()->createCollection($this->childNodes);
  }
  // public function getChildren($sNamespace = null, $iDepth = null, $bCleanComments = false)
  // public function countChildren()

  public function hasChildren() {

    return $this->hasChildNodes();
  }

  public function countChildren() {

    return $this->childNodes->length;
  }

  public function set() {

    $mResult = null;

    if (func_num_args() > 1) {

      $this->set(func_get_arg(0));
      $mResult = array();

      for ($i = 1; $i < func_num_args(); $i++) $mResult[] = $this->add(func_get_arg($i));

    }
    else if (func_num_args() == 1) {

      $mValue = func_get_arg(0);

      $this->set();
      $mResult = $this->add($mValue);
    }
    else {

      if ($this->hasChildren()) {

        foreach ($this->getChildren() as $child) $child->remove();
      }
    }

    return $mResult;
  }

  public function add() {

    return $this->insert(func_get_args());
  }

  public function createElement($sName, $mContent = '', array $aAttributes = array(), $sNamespace = null) {

    $handler = $this->getHandler();

    if (!$sNamespace) $sNamespace = $this->getNamespace();

    $result = $handler->createElement($sName, $mContent, $aAttributes, $sNamespace);

    return $result;
  }

  public function addElement($sName, $mContent = '', array $aAttributes = array(), $sNamespace = null) {

    $result = $this->insertChild($this->createElement($sName, $mContent, $aAttributes, $sNamespace));

    return $result;
  }
  // *public function isFirst()
  // *public function isLast()
  // public function isRoot()
  // public function isEmpty()

  /**
   * Test wether element has element children or not
   * @return boolean TRUE if the element contains children element (opposite to text)
   */
  public function isComplex() {

    return ($this->hasChildren() && ($this->getChildren()->length > 1 || $this->getFirst() instanceof dom\element));
  }

  /**
   * Test wether element has text child or not
   * @return boolean TRUE if the element contains child text (opposite to text)
   */
  public function isSimple() {

    return ($this->hasChildren() && !$this->isComplex());
  }

  public function isElement($sName, $sNamespace = null) {

    if ($sName != $this->getName()) return false;
    if ($sNamespace) return $this->getNamespace() == $sNamespace;

    return true;
  }

  public function getLast() {

    return $this->lastChild;
  }

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

        $result = $this->getHandler()->set($mContent);

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

  public function getNext() {

    return $this->nextSibling;
  }

  public function getPrevious() {

    return $this->previousSibling;
  }

  public function lookupNamespace($sPrefix = null) {

    return $this->lookupNamespaceURI($sPrefix);
  }

  public function getNamespace() {

    return $this->namespaceURI;
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
  private static function compareAttributes($el1, $el2) {

    if ($el2->getAttributes()->length > $el1->getAttributes()->length) {

      $eltmp = $el1;
      $el1 = $el2;
      $el2 = $eltmp;
    }

    foreach ($el1->getAttributes() as $attribute) {

      if (substr($attribute->getName(), 0, 6) == 'xmlns:') continue;

      $compare = $el2->loadAttribute($attribute->getName(), $attribute->getNamespace(), false);
      if (!$compare || $compare->getValue() != $attribute->getValue()) {

        return $attribute;
      }
    }

    return true;
  }

  /**
   * Compare two elements and their content, ignore xmlns attributes
   * Not identical, but relevant to @method isEqualNode()
   *
   * @param dom\element $element The element to compare with this one
   * @param? array $aPath The previous compared element for backtrace debug
   *
   * @return integer @const self::COMPARE_SUCCESS, @const self::COMPARE_BAD_ELEMENT, @const self::COMPARE_BAD_CHILD, @const self::COMPARE_BAD_ATTRIBUTE
   */
  public function compare(dom\element $element, $aPath = array()) {

    $aPath[] = $this->getName();

    $this->compareBadNode = $this;

    if ($element->getType() == self::TEXT ||
      !($this->getName() == $element->getName()) ||
      !($this->getNamespace() == $element->getNamespace())) {

      $this->compareBadNode = $this;
      return self::COMPARE_BAD_ELEMENT;
    }

    $attribute = self::compareAttributes($this, $element);

    if ($attribute !== true) {

      $this->compareBadNode = $attribute;
      return self::COMPARE_BAD_ATTRIBUTE;
    }

    if ($this->getChildren()->length != $element->getChildren()->length) {

      $this->compareBadNode = $this;
      return self::COMPARE_BAD_CHILD;
    }
    else {

      foreach ($element->getChildren() as $iKey => $child) {

        if ($selfChild = $this->getChildren()->item($iKey)) {

          $iResult = $selfChild->compare($child, $aPath);

          if ($iResult !== self::COMPARE_SUCCESS) {

            if ($selfChild->getType() == self::ELEMENT) $this->compareBadNode = $selfChild->compareBadNode;

            return self::COMPARE_BAD_CHILD;
          }
        }
        else {

          $this->compareBadNode = $this;
          return self::COMPARE_BAD_CHILD;
        }
      }
    }

    return self::COMPARE_SUCCESS;
  }


  // public function updateNamespaces($mFrom = null, $mTo = null, $mPrefix = '', $oParent = null)

  protected function getPath() {

    $sResult = '';

    //$sLine = '';
    //if (method_exists($this, 'getLineNo')) $sLine = $this->getLineNo();

    //if (!$sLine) $sLine = 'xx';
    $sResult .= $this->getNamespace() . ':' . $this->getName(true);
    //$sResult .= $this->getName(false);

    // if @id or @name, display it

    if ($sID = $this->getAttribute('id')) $sResult .= '[@id = ' . $sID . ']';
    else if ($sName = $this->getAttribute('name')) $sResult .= '[@name = ' . $sName . ']';

    //$sResult .=  ' (line ' . $sLine . ')';

    return $sResult;
  }

  public function asToken() {

    if (\Sylma::read('dom/debug/token')) {

      $sResult = '[element]';
    }
    else {

      $sResult = ' @element ' . $this->getPath() . ' in ' . $this->getHandler()->asToken() . ':' . $this->getLineNo();
    }

    return $sResult;
  }

  public function prepareHTML($iLevel = 0) {

    $iMaxLength = 20;

    $iPrevious = $this->getPrevious() ? $this->getPrevious()->getType() : '';
    $iNext = $this->getNext() ? $this->getNext()->getType() : '';

    if (!$this->isRoot() && $iPrevious != self::TEXT && $iNext != self::TEXT) {

      $this->getParent()->insert("\n".str_repeat('  ', $iLevel), $this);
    }

    foreach ($this->getChildren() as $child) {

      if ($child instanceof dom\element) {

        $child->prepareHTML($iLevel + 1);
      }
      else if ($child instanceof dom\cdata || $child instanceof dom\comment) {

        $child->getParent()->insert("\n".str_repeat('  ', $iLevel + 1), $child);
      }
    }

    if ($this->hasChildren() && $this->isComplex()) {

      if (strlen($this->asString()) > $iMaxLength) $this->add("\n".str_repeat('  ', $iLevel));
    }
  }

  protected function throwException($sMessage, array $mSender = array()) {

    $mSender[] = $this->asToken();

    if (!$controler = $this->getHandler()) {

      $controler = $this->getControler();
    }

    $controler->throwException($sMessage, $mSender);
  }

  public function asString($iMode = 0) {

    return $this->getHandler()->elementAsString($this, $iMode);
  }

  public function __toString() {

    return $this->nodeValue;
  }
}

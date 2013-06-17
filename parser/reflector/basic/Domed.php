<?php

namespace sylma\parser\reflector\basic;
use \sylma\core, sylma\dom, sylma\storage\fs, symla\parser\reflector;

abstract class Domed extends Componented {

  const BUILDER_NS = 'http://2013.sylma.org/parser/reflector/builder';
  CONST PREFIX = null;

  protected $allowForeign = false;
  protected $allowUnknown = false;
  protected $allowText = false;

  protected $node;
  protected $elementDocument;

  /**
   * Handler for element creation with NS bug fixes
   */
  protected $documentContainer;

  /**
   *
   * @return dom\element
   */
  public function getNode($bDebug = true) {

    if ($this->elementDocument) {

      $result = $this->elementDocument->getRoot();
    }
    else {

      $result = $this->node;
    }

    if ($bDebug && !$result) {

      $this->launchException('No node defined');
    }

    return $result;
  }

  /**
   *
   * @param $el Main element, can be used with queryx(), getx(), readx()
   * @param bool $bClone Element is cloned, parents are lost
   * @param bool $bNamespace Element namespace becomes main namespace and
   *    handler namespaces are loaded into class
   * @return \sylma\dom\element
   */
  public function setNode(dom\element $el, $bClone = false, $bNamespace = true) {

    $aNS = $el->getHandler()->getNS();

    if ($bNamespace) {

      $this->setNamespaces($aNS);
      if (static::PREFIX) $this->setNamespace($el->getNamespace(), static::PREFIX);
    }

    if ($bClone) {

      $doc = $this->createDocument($el);
      $result = $doc->getRoot();

      //$doc->registerNamespaces($aNS);
      //$this->registerNamespaces($result);

      $this->elementDocument = $doc;
      $this->node = $result;
    }
    else {

      if ($bNamespace) $el->getHandler()->registerNamespaces($this->getNS());

      $result = $this->node = $el;
      //$this->registerNamespaces($result);
    }


    return $result;
  }

  protected function registerNamespaces(dom\element $el) {

    $el->getHandler()->registerNamespaces($this->getNS());
  }

  protected function queryx($sPath, $bDebug = false, array $aNS = array()) {

    return $this->getNode()->queryx($sPath, $aNS, $bDebug);
  }

  protected function getx($sPath, $bDebug = false, array $aNS = array()) {

    return $this->getNode()->getx($sPath, $aNS, $bDebug);
  }

  protected function readx($sPath = '', $bDebug = false, array $aNS = array()) {

    return $this->getNode()->readx($sPath, $aNS, $bDebug);
  }

  protected function getDocumentContainer() {

    if (!$this->documentContainer) {

      $this->documentContainer = $this->getManager('dom')->createDocument();
      $this->documentContainer->addElement('root');
    }

    return $this->documentContainer;
  }

  protected function createElement($sName, $mContent = null, array $aAttributes = array(), $sNamespace = '') {

    if (!$sNamespace) {

      $this->throwException('Element without namespace vorbidden');
    }

    $el = $this->getDocumentContainer()->createElement($sName, $mContent, $aAttributes, $sNamespace);

    return $el;
  }

  protected function parseNode(dom\node $node) {

    $mResult = null;

    switch ($node->getType()) {

      case $node::ELEMENT :

        $mResult = $this->parseElement($node);

      break;

      case $node::TEXT :

        $mResult = $this->parseText($node);

      break;

      case $node::COMMENT :

      break;

      default :

        $this->throwException(sprintf('Unknown node type : %s', $node->getType()));
    }

    return $mResult;
  }

  /**
   *
   * @param dom\element $el
   * @return type core\argumentable|array|null
   */
  protected function parseElement(dom\element $el) {

    $mResult = null;

    if ($this->useNamespace($el->getNamespace())) {

      $mResult = $this->parseElementSelf($el);
    }
    else {

      $mResult = $this->parseElementForeign($el);
    }

    return $mResult;
  }

  protected function parseElementSelf(dom\element $el) {

    return $this->parseComponent($el);
  }

  protected function allowForeign($mValue = null) {

    if (!is_null($mValue)) $this->allowForeign = $mValue;
    return $this->allowForeign;
  }

  protected function parseElementForeign(dom\element $el) {

    return $this->parseElementUnknown($el);
  }

  protected function allowUnknown($mValue = null) {

    if (!is_null($mValue)) $this->allowUnknown = $mValue;
    return $this->allowUnknown;
  }

  protected function parseElementUnknown(dom\element $el) {

    $this->throwException(sprintf('Uknown %s not allowed', $el->asToken()));
  }

  protected function allowText($mValue = null) {

    if (!is_null($mValue)) $this->allowText = $mValue;
    return $this->allowText;
  }

  protected function parseText(dom\text $node, $bTrim = true) {

    if (!$this->allowText()) {

      $this->throwException('Text node not allowed here', array($node->asToken()));
    }

    return $bTrim ? $this->trimString($node->getValue()) : $node->getValue();
  }

  protected function trimString($sValue) {

    return preg_replace('!([ \t])+!', '\1', $sValue);
  }

  /**
   * TODO? : rename parseCollection, parseCollectionElement and parseCollectionText (+node,+...)
   * @param $children
   * @return array
   */
  protected function parseChildren(dom\collection $children) {

    $aResult = $mResult = array();

    while ($child = $children->current()) {

      switch ($child->getType()) {

        case $child::ELEMENT :

          $this->parseChildrenElement($child, $aResult);

          break;

        case $child::TEXT :

          $this->parseChildrenText($child, $aResult);

          break;

        case $child::COMMENT : break;

        default :

          $this->throwException('Node type not allowed here', array($child->asToken()));
      }

      $children->next();
    }
    //$this->show($aResult, false);

    return $aResult;
  }

  protected function parseChildrenElement(dom\element $el, array &$aResult, $bRoot = false) {

    try {

      if ($this->useNamespace($el->getNamespace())) {

        $this->parseChildrenElementSelf($el, $aResult);
      }
      else {

        $this->parseChildrenElementForeign($el, $aResult);
      }
    }
    catch (core\exception $e) {

      $e->addPath($el->asToken());
      throw $e;
    }
  }
  
  /**
   * Browsing function, result is not returned but added to $aResult,
   *
   * @param $el
   * @param array $aResult
   */
  protected function parseChildrenElementSelf(dom\element $el, array &$aResult) {

    $mResult = $this->parseElementSelf($el);
    $this->addParsedChild($el, $aResult, $mResult);
  }

  /**
   * Browsing function, result is not returned but added to $aResult,
   *
   * @param $el
   * @param array $aResult
   */
  protected function parseChildrenElementForeign(dom\element $el, array &$aResult) {

    $mResult = $this->parseElementForeign($el);
    $this->addParsedChild($el, $aResult, $mResult);
  }

  protected function addParsedChild(dom\element $el, array &$aResult, $mContent) {

    if (!is_null($mContent)) $aResult[] = $mContent;
  }

  /**
   * Browsing function, result is not returned but added to $aResult
   * @param $node
   * @param array $aResult
   */
  protected function parseChildrenText(dom\text $node, array &$aResult) {

    $aResult[] = $this->parseText($node);
  }

  protected function parseAttribute(dom\attribute $attr) {

    return $attr;
  }

  protected function useForeignAttributes(dom\element $el) {

    $bResult = false;

    foreach ($el->getAttributes() as $attr) {

      $sNamespace = $attr->getNamespace();

      if ($sNamespace && $sNamespace != $this->getNamespace(static::PREFIX) && $sNamespace !== self::BUILDER_NS) {

        $bResult = true;
        break;
      }
    }

    return $bResult;
  }
}

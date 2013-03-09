<?php

namespace sylma\parser\reflector\basic;
use \sylma\core, sylma\dom, sylma\storage\fs, symla\parser\reflector;

abstract class Domed extends Componented {

  CONST PREFIX = null;

  protected $allowForeign = false;
  protected $allowUnknown = false;

  protected $element;
  protected $elementDocument;

  /**
   * Handler for element creation with NS bug fixes
   */
  protected $documentContainer;

  /**
   *
   * @return dom\element
   */
  public function getNode() {

    if ($this->elementDocument) {

      $result = $this->elementDocument->getRoot();
    }
    else {

      $result = $this->element;
    }

    return $result;
  }

  public function setNode(dom\element $el, $bClone = true, $bNamespace = true) {

    if ($bClone) {

      if ($bNamespace) $this->setNamespace($el->getNamespace(), static::PREFIX);

      $doc = $this->createDocument($el);
      $doc->registerNamespaces($el->getHandler()->getNS());
      $doc->registerNamespaces($this->getNS());

      $result = $doc->getRoot();

      $this->elementDocument = $doc;
      $this->element = $result;
    }
    else {

      $result = $this->element = $el;
    }

    return $result;
  }

  protected function queryx($sPath, array $aNS = array(), $bDebug = false) {

    return $this->getNode()->queryx($sPath, $aNS, $bDebug);
  }

  protected function getx($sPath, array $aNS = array(), $bDebug = false) {

    return $this->getNode()->getx($sPath, $aNS, $bDebug);
  }

  protected function readx($sPath, array $aNS = array(), $bDebug = false) {

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

    $this->throwException(sprintf('Uknown %s not recognized', $el->asToken()));
  }

  protected function parseText(dom\text $node) {

    return (string) $node;
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

          try {

            if ($this->useNamespace($child->getNamespace())) {

              $this->parseChildrenElementSelf($child, $aResult);
            }
            else {

              $this->parseChildrenElementForeign($child, $aResult);
            }
          }
          catch (core\exception $e) {

            $e->addPath($child->asToken());
            throw $e;
          }

          break;

        case $child::TEXT :

          $this->parseChildrenText($child, $aResult);

          break;

        default :

          $this->throwException('Node type not allowed here', array($child->asToken()));
      }

      $children->next();
    }
    //$this->show($aResult, false);

    return $aResult;
  }

  /**
   * Browsing function, result is not returned but added to $aResult,
   *
   * @param $el
   * @param array $aResult
   */
  protected function parseChildrenElementSelf(dom\element $el, array &$aResult) {

    $mResult = $this->parseElementSelf($el);

    if (!is_null($mResult)) $aResult[] = $mResult;
  }

  /**
   * Browsing function, result is not returned but added to $aResult,
   *
   * @param $el
   * @param array $aResult
   */
  protected function parseChildrenElementForeign(dom\element $el, array &$aResult) {

    $mResult = $this->parseElementForeign($el);

    if (!is_null($mResult)) $aResult[] = $mResult;
  }

  /**
   * Browsing function, result is not returned but added to $aResult
   * @param $node
   * @param array $aResult
   */
  protected function parseChildrenText(dom\text $node, array &$aResult) {

    $this->throwException('Text node not allowed here', array($node->asToken()));
  }

  protected function parseAttribute(dom\attribute $attr) {

    return $attr;
  }

  protected function useForeignAttributes(dom\element $el) {

    $bResult = false;

    foreach ($el->getAttributes() as $attr) {

      $sNamespace = $attr->getNamespace();

      if ($sNamespace && $sNamespace != $this->getNamespace(static::PREFIX)) {

        $bResult = true;
        break;
      }
    }

    return $bResult;
  }
}

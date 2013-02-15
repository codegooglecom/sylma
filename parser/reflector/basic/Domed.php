<?php

namespace sylma\parser\reflector\basic;
use \sylma\core, sylma\dom, sylma\storage\fs, symla\parser\reflector;

abstract class Domed extends Namespaced {

  CONST PREFIX = null;

  protected $allowComponent = false;
  protected $allowForeign = false;
  protected $allowUnknown = false;

  protected function allowComponent($mValue = null) {

    if (!is_null($mValue)) $this->allowComponent = $mValue;
    return $this->allowComponent;
  }

  abstract protected function parseComponent(dom\element $el);

  protected function createComponent(dom\element $el, $parser) {

    $class = $this->getFactory()->findClass($el->getName());
    $sName = 'component/' . $el->getName();

    return $this->create($sName, array($parser, $el, $class, false, $this->allowForeign(), $this->allowUnknown()));
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

    if ($this->allowComponent()) {

      $result = $this->parseComponent($el);
    }
    else {

      $this->throwException('Cannot handle sub element');
    }

    return $result;
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

    return $this->getWindow()->createString((string) $node);
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

            $this->parseChildrenElement($child, $aResult);
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
   * @see @method parseElement()
   *
   * @param $el
   * @param array $aResult
   */
  protected function parseChildrenElement(dom\element $el, array &$aResult) {

    $mResult = $this->parseElement($el);

    //if (is_null($mResult)) $this->throwException (sprintf('NULL value not accepted with %s', $el->asToken ()));
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

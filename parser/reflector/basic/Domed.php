<?php

namespace sylma\parser\reflector\basic;
use \sylma\core, sylma\parser\languages\common, sylma\dom, sylma\parser;

\Sylma::load('Child.php', __DIR__);

abstract class Domed extends Child {

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

    $sNamespace = $el->getNamespace();
    $mResult = null;

    if ($sNamespace == $this->getNamespace()) {

      $mResult = $this->parseElementSelf($el);
    }
    else {

     $mResult = $this->parseElementForeign($el);
    }

    return $mResult;
  }

  abstract protected function parseElementSelf(dom\element $el);

  protected function parseElementForeign(dom\element $el) {

    //$this->throwException('Foreign element not allowed here');
    return $this->parseElementUnknown($el);
  }

  protected function parseElementUnknown(dom\element $el) {

    return $el;
  }

  protected function parseText(dom\text $node) {

    return $this->getWindow()->createString((string) $node);
  }

  /**
   * Parse children into main context. Insert results
   * @param dom\element $el
   * @return array
   */
  protected function parseChildren(dom\collection $children) {

    $aResult = $mResult = array();
//echo get_class($this);
    while ($child = $children->current()) {
      //$this->show($child);
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

        case $child::TEXT : $this->parseChildrenText($child, $aResult); break;
        default :

          $this->throwException('Node type not allowed here', array($child->asToken()));
      }

      $children->next();
    }
    //$this->show($aResult, false);

    return $aResult;
  }

  protected function parseChildrenElement(dom\element $el, &$aResult) {

    $mResult = $this->parseElement($el);

    //if (is_null($mResult)) $this->throwException (sprintf('NULL value not accepted with %s', $el->asToken ()));

    if (!is_null($mResult)) $aResult[] = $mResult;
  }

  protected function parseChildrenText(dom\text $node) {

    $this->throwException('Text node not allowed here', array($node->asToken()));
  }

  /**
   *
   * @param dom\element $el
   * @return dom\node
   */
  protected function parseAttributes(dom\element $el, dom\handler $resultHandler) {

    $aForeigns = array();
    $result = $resultHandler;

    foreach ($el->getAttributes() as $attr) {

      $sNamespace = $attr->getNamespace();

      if (!$sNamespace || $sNamespace == $this->getNamespace()) {

        $resultHandler->add($this->parseAttribute($attr));
      }
      else {

        $aForeigns[$sNamespace] = true;
      }
    }

    foreach ($aForeigns as $sNamespace => $bVal) {

      $parser = $this->loadParser($sNamespace, 'attribute');
      $result = $parser->parseAttributes($el, $result->getRoot(), $resultHandler);
    }

    return $result;
  }

  protected function parseAttribute(dom\attribute $attr) {

    return $attr;
  }

  protected function useForeignAttributes(dom\element $el) {

    $bResult = false;

    foreach ($el->getAttributes() as $attr) {

      $sNamespace = $attr->getNamespace();

      if ($sNamespace && $sNamespace != $this->getNamespace()) {

        $bResult = true;
        break;
      }
    }

    return $bResult;
  }

}

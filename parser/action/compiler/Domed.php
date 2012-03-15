<?php

/**
* This file is part of the PHP framework Sylma : http://www.sylma.org
*
* @copyright 2012 Rodolphe Gerber [rodolphe.gerber@sylma.org]
* @licence http://www.gnu.org/licenses/gpl.html General Public Licence version 3
*/

namespace sylma\parser\action\compiler;
use sylma\core, sylma\dom, sylma\parser, sylma\parser\action\php;

require_once('Runner.php');
require_once('parser/elemented.php');

abstract class Domed extends Runner implements parser\elemented {

  protected $currentElement;

  protected function parseDocument(dom\document $doc) {

    $aResult = array();

    if ($doc->isEmpty()) {

      $this->throwException(t('empty doc'));
    }

    $doc->registerNamespaces($this->getNS());

    $settings = $doc->getx('self:settings', array(), false);

    // arguments

    if ($settings) {

      $this->getWindow()->add($this->reflectSettings($settings));
      $settings->remove();
    }

    $aResult = $this->parseChildren($doc->getChildren());

    return $aResult;
  }

  protected function parseNode(dom\node $node) {

    $mResult = null;

    switch ($node->getType()) {

      case $node::ELEMENT :

        $mResult = $this->parseElement($node);

      break;

      case $node::TEXT :

        $mResult = $this->getWindow()->createString((string) $node);

      break;

      case $node::COMMENT :

      break;

      default :

        $this->throwException(txt('Unknown node type : %s', $node->getType()));
    }

    return $mResult;
  }

  public function parse(dom\node $node) {

    return $this->parseNode($node);
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

      $mResult = $this->parseElementAction($el);
    }
    else {

      $mResult = $this->parseElementForeign($el, $this->getControler()->create('document'));
    }

    return $mResult;
  }

  /**
   *
   * @param dom\element $el
   * @return dom\node|array|null
   */
  protected function parseElementForeign(dom\element $el, dom\complex $parent) {

    $mResult = null;

    if ($el->getNamespace() == $this->getNamespace('class')) {

      $mResult = $this->reflectSelfCall($el);
    }
    else if ($parser = $this->getParser($el->getNamespace())) {

      $mResult = $parser->parse($el);
    }
    else {

      $this->useTemplate(true);

      $newElement = $parent->addElement($el->getName(), null, array(), $el->getNamespace());

      if ($this->useForeignAttributes($el)) {

        $mResult = $this->parseAttributes($el, $newElement->getHandler());
      }
      else {

        if ($el->getAttributes()->length) {

          $newElement->add($el->getAttributes());
        }

        $mResult = $newElement->getHandler();
      }

      if ($aChildren = $this->parseChildren($el->getChildren())) {

        $newElement->add($aChildren);
      }
    }

    return $mResult;
  }

  /**
   * Parse children into main context. Insert results
   * @param dom\element $el
   * @return array
   */
  protected function parseChildren(dom\collection $children) {

    $aResult = array();

    while ($child = $children->current()) {

      if ($child->getType() != $child::ELEMENT) {

        $aResult[] = $child;
      }
      else if ($mResult = $this->parseElement($child)) {

        if (!$mResult instanceof dom\node && !$mResult instanceof php\structure) {

          if (is_array($mResult)) {

            $mResult = $this->getWindow()->argToInstance($mResult);
          }

          $mResult = $this->getWindow()->createInsert($mResult, $this->useString());
        }

        $aResult[] = $mResult;
      }

      $children->next();
    }

    return $aResult;
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

        $resultHandler->add($attr);
      }
      else {

        $aForeigns[$sNamespace] = true;
      }
    }

    foreach ($aForeigns as $sNamespace => $bVal) {

      if ($parser = $this->getParser($sNamespace)) {

        if (!$parser instanceof parser\attributed) {

          $this->throwException(txt('Cannot use parser %s with attributes', $sNamespace));
        }

        $result = $parser->parseAttributes($el, $result->getRoot(), $resultHandler);
      }
    }

    return $result;
  }

  protected function parseAttribute(dom\attribute $attr) {

    return $attr;
  }
}

<?php

namespace sylma\parser\action\compiler;
use \sylma\core, \sylma\dom;

require_once('Action.php');

abstract class Domed extends Action {

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

    $aResult = $this->parseChildren($doc);

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

      $mResult = $this->parseElementForeign($el);
    }

    return $mResult;
  }

  /**
   *
   * @param dom\element $el
   * @return dom\node|array|null
   */
  protected function parseElementForeign(dom\element $el) {

    $mResult = null;

    if ($el->getNamespace() == $this->getNamespace('element')) {

      $mResult = $this->reflectSelfCall($el);
    }
    else if ($parser = $this->getParser($el->getNamespace())) {

      $mResult = $parser->parse($el);
    }
    else {

      $this->useTemplate(true);

      $mResult = $this->getControler()->create('document');
      $mResult->addElement($el->getName(), null, array(), $el->getNamespace());

      if ($aAttr = $this->parseAttributes($el)) $mResult->add($aAttr);

      if ($aChildren = $this->parseChildren($el)) $mResult->add($aChildren);
    }

    return $mResult;
  }

  /**
   * Parse children into main context. Insert results
   * @param dom\element $el
   * @return array
   */
  protected function parseChildren(dom\complex $el) {

    $aResult = array();

    foreach ($el->getChildren() as $child) {

      if ($child->getType() != $child::ELEMENT) {

        $aResult[] = $child;
      }
      else if ($mResult = $this->parseElement($child)) {

        if (!$mResult instanceof dom\node) {

          $mResult = $this->getWindow()->createInsert($mResult, $this->useString());
        }

        $aResult[] = $mResult;
      }
    }

    return $aResult;
  }
}

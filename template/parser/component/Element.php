<?php

namespace sylma\template\parser\component;
use sylma\core, sylma\dom, sylma\parser\reflector, sylma\parser\languages\common;

class Element extends Unknowned implements common\arrayable, common\argumentable {

  public function parseRoot(dom\element $el) {

    $this->setNode($el, true, false);
  }

  protected function parseAttributes(dom\element $el) {

    $aResult = array();
    $attrs = $el->getAttributes();

    foreach ($attrs as $attr) {

      $aResult[] = ' ';
      $aResult[] = $this->parseAttribute($attr);
    }

    return $aResult;
  }

  protected function parseAttribute(dom\attribute $attr) {

    $aResult = array();

    $aResult[] = $attr->getName();
    $aResult[] = '="';
    $aResult[] = $this->parseAttributeValue($attr->getValue());
    $aResult[] = '"';

    return $aResult;
  }

  protected function parseAttributeValue($sValue) {

    return $sValue;
  }

  protected function complexAsArray(dom\element $el) {

    $aResult = array();

    if ($el->countChildren() > 1) {

      $aChildren = $this->parseComponentRoot($el);
    }
    else {

      $aChildren = array($this->parseComponentRoot($el));
    }

    $aResult[] = '<' . ($el->getPrefix() ? $el->getPrefix() . ':' : '') . $el->getName();
    $aResult[] = $this->parseAttributes($el);
    $aResult[] = '>';

    foreach ($aChildren as $child) {

      $aResult[] = $child;
    }

    $aResult[] = '</' . $el->getName() . '>';

    return $aResult;
  }

  public function asArray() {

    $el = $this->getNode();

    if ($el->hasChildren()) {

      $aResult = $this->complexAsArray($el);
    }
    else {

      $aResult = array();
      $aResult[] = '<' . ($el->getPrefix() ? $el->getPrefix() . ':' : '') . $el->getName();
      $aResult[] = $this->parseAttributes($el);
      $aResult[] = '/>';
    }

    return $aResult;
  }

  public function asArgument() {

    $var = $this->addToResult($this->toString($this->asArray()), false);

    return $var->asArgument();
  }
}


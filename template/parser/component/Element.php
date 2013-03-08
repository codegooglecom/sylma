<?php

namespace sylma\template\parser\component;
use sylma\core, sylma\dom, sylma\parser\reflector, sylma\parser\languages\common;

class Element extends Unknowned implements common\arrayable, common\argumentable {

  //protected $aAttributes = array();
  protected $aContent = array();
  protected $bBuilded = false;

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

  protected function build() {

    if (!$this->bBuilded) {

      $el = $this->getNode();

      if ($el->countChildren()) {

        if ($el->countChildren() > 1) {

          $aContent = $this->parseComponentRoot($el);
        }
        else {

          $aContent = array($this->parseComponentRoot($el));
        }

        $this->aContent = $aContent;
      }

      $this->bBuilded = true;
    }

    return $this->aContent;
  }

  protected function complexAsArray(dom\element $el) {

    $aResult = array();

    $aChildren = $this->build();

    $aResult[] = '<' . ($el->getPrefix() ? $el->getPrefix() . ':' : '') . $el->getName();
    $aResult[] = $this->parseAttributes($el);
    $aResult[] = '>';

    foreach ($aChildren as $child) {

      $aResult[] = $child;
    }

    $aResult[] = '</' . $el->getName() . '>';

    return $aResult;
  }

  protected function simpleAsArray(dom\element $el) {

    $this->build();

    $aResult = array();
    $aResult[] = '<' . ($el->getPrefix() ? $el->getPrefix() . ':' : '') . $el->getName();
    $aResult[] = $this->parseAttributes($el);
    $aResult[] = '/>';
  }

  public function asArray() {

    $el = $this->getNode();

    if ($el->hasChildren()) {

      $aResult = $this->complexAsArray($el);
    }
    else {

      $aResult = $this->simpleAsArray($el);
    }

    return $aResult;
  }

  public function asArgument() {

    $var = $this->addToResult($this->toString($this->asArray()), false);

    return $var->asArgument();
  }
}


<?php

namespace sylma\template\parser\component;
use sylma\core, sylma\dom, sylma\parser\reflector, sylma\parser\languages\common, sylma\template as template_ns;

class Element extends Unknowned implements common\arrayable, common\argumentable, template_ns\parser\component, template_ns\element, common\addable {

  //protected $aAttributes = array();
  protected $aContent = array();
  protected $bBuilded = false;

  public function parseRoot(dom\element $el) {

    $this->build($el);
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

    preg_match_all('/{([^}]+)}/', $sValue, $aMatches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER);

    if ($aMatches) {

      $mResult = array();

      foreach ($aMatches as $aResult) {

        $iVarLength = strlen($aResult[0][0]);
        $val = $this->getTree()->reflectApply($aResult[1][0]);

        $sStart = substr($sValue, 0, $aResult[0][1]);
        $sEnd = substr($sValue, $aResult[0][1] + $iVarLength);

        $mResult[] = array($sStart, $val, $sEnd);
      }
    }
    else {

      $mResult = $sValue;
    }

    return $mResult;
  }

  public function build(dom\element $el = null) {

    if (!$this->bBuilded) {

      $this->setNode($el, true, false);

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

  public function onAdd() {


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

    //$this->build();

    $aResult = array();
    $aResult[] = '<' . ($el->getPrefix() ? $el->getPrefix() . ':' : '') . $el->getName();
    $aResult[] = $this->parseAttributes($el);
    $aResult[] = '/>';

    return $aResult;
  }

  public function setAttributes(array $aAttrs) {

    return $this->getNode()->setAttributes($aAttrs);
  }

  public function setAttribute($sName, $sValue) {

    return $this->getNode()->setAttribute($sName, $sValue);
  }

  public function readAttribute($sName) {

    return $this->getNode()->readAttribute($sName);
  }

  public function addToken($sAttribute, $sValue) {

    return $this->getNode()->addToken($sAttribute, $sValue);
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

    $assign = $this->getParser()->addToResult($this->asArray(), false);

    return $assign->asArgument();
  }
}


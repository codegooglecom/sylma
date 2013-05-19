<?php

namespace sylma\template\parser\component;
use sylma\core, sylma\dom, sylma\parser\reflector, sylma\parser\languages\common, sylma\template as template_ns;

class Element extends Unknowned implements common\arrayable, common\argumentable, template_ns\parser\component, template_ns\element, common\addable, core\tokenable {

  const TARGET_PREFIX = 'target';

  //protected $aAttributes = array();
  protected $aContent = array();
  protected $bBuilded = false;

  protected $aDefaultAttributes = array();
  protected $aAttributes = array();

  public function parseRoot(dom\element $el) {

    $this->allowUnknown(true);
    $this->allowText(true);

    $this->build($el);
    //$this->setNamespace(\Sylma::read('namespaces/html'));
  }

  protected function parseAttributes() {

    $aResult = array();

    foreach ($this->getAttributes() as $sName => $aValue) {

      $aResult[] = ' ';
      $aResult[] = $this->parseAttributeArray($sName, $aValue);
    }

    return $aResult;
  }

  protected function parseAttributeArray($sName, array $aValue) {

    $aResult = array();

    $aResult[] = $sName;
    $aResult[] = '="';

    foreach ($this->getWindow()->parseArrayables($aValue) as $mVal) {

      if (is_string($mVal)) $aResult[] = $this->parseAttributeValue($mVal);
      else $aResult[] = $mVal;
    }

    $aResult[] = '"';

    return $aResult;
  }

  protected function parseAttributeValue($sValue) {

    preg_match_all('/{([^}]+)}/', $sValue, $aMatches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER);

    if ($aMatches) {

      $mResult = array();

      foreach ($aMatches as $aResult) {

        $iVarLength = strlen($aResult[0][0]);
        $val = $this->getTemplate()->applyPath($aResult[1][0], '');

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
      $this->start();

      $el = $this->getNode();

      foreach ($el->getAttributes() as $attr) {

        $this->setDefaultAttribute($attr->getName(), $attr->getValue());
      }

      $this->resetAttributes();

      if ($el->countChildren()) {

        if ($el->countChildren() > 1) {

          $aContent = $this->parseComponentRoot($el);
        }
        else {

          $aContent = array($this->parseComponentRoot($el));
        }

        $this->aContent = $aContent;
      }

      $this->stop();

      $this->bBuilded = true;
    }

    return $this->aContent;
  }

  public function onAdd() {

    //$this->getWindow()->loadContent($this->build());
  }

  protected function start() {

    return $this->getRoot()->startElement($this);
  }

  protected function stop() {

    return $this->getRoot()->stopElement();
  }

  protected function loadName(dom\element $el) {

    //$sName = ($el->getPrefix() ? $el->getPrefix() . ':' : '') . $el->getName();
    //$sName = $this->getPrefix($el->getNamespace()) . $el->getName();
    $sName = $el->getName();

    return $sName;
  }

  protected function complexAsArray(dom\element $el) {

    $aResult = $aContent = array();

    $aChildren = $this->build();

    $this->start();

    foreach ($aChildren as $child) {

      $aContent[] = $this->getWindow()->parseArrayables(array($child));
    }

    $sName = $this->loadName($el);


    $aResult[] = '<' . $sName;
    $aResult[] = $this->parseAttributes();
    $aResult[] = '>';

    $aResult[] = $aContent;

    $aResult[] = '</' . $sName . '>';

    $this->stop();

    return $aResult;
  }

  protected function simpleAsArray(dom\element $el) {

    //$this->build();

    $aResult = array();
    $aResult[] = '<' . ($el->getPrefix() ? $el->getPrefix() . ':' : '') . $el->getName();
    $aResult[] = $this->parseAttributes();
    $aResult[] = '/>';

    return $aResult;
  }

  public function setAttributes(array $aAttrs) {

    $this->aAttributes = $aAttrs;
  }

  public function setDefaultAttribute($sName, $mVal) {

    if (!is_array($mVal)) $mVal = array($mVal);

    $this->aDefaultAttributes[$sName] = $mVal;
  }

  protected function resetAttributes() {

    $this->aAttributes = $this->aDefaultAttributes;
  }

  public function setAttribute($sName, $mVal) {

    if (!is_array($mVal)) $mVal = array($mVal);

    $this->aAttributes[$sName] = $mVal;
  }

  protected function getAttributes() {

    return $this->aAttributes;
  }

  public function readAttribute($sName) {

    return $this->aAttributes[$sName];
  }

  public function addToken($sAttribute, $mVal) {

    if (isset($this->aAttributes[$sAttribute])) {

      $mContent = array(' ', $mVal);
    }
    else {

      $mContent = $mVal;
    }

    $this->aAttributes[$sAttribute][] = $mContent;
    //$this->aAttributes[$sAttribute][] = array($mVal);
  }

  public function asArray() {

    $el = $this->getNode();

    if ($el->hasChildren()) {

      $aResult = $this->complexAsArray($el);
    }
    else {

      $aResult = $this->simpleAsArray($el);
    }

    $this->resetAttributes();

    return $aResult;
  }

  public function asArgument() {

    $assign = $this->getParser()->addToResult($this->asArray(), false);

    return $assign->asArgument();
  }

  public function asToken() {

    return $this->getNode(false) ? $this->getNode()->asToken() : '[No node defined]';
  }
}


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

    foreach ($this->getAttributes() as $sName => $mVal) {

      $aResult[] = ' ';
      $aResult[] = is_array($mVal) ? $this->parseAttributeArray($sName, $mVal) : $mVal;
    }

    //$this->getWindow()->loadContent($aResult);

    return $this->getWindow()->parseArrayables($aResult);
  }

  protected function parseAttributeArray($sName, array $aValue) {

    $aResult = array();
    $bFirst = true;

    $aResult[] = $sName;
    $aResult[] = '="';

    foreach ($this->getWindow()->parseArrayables($aValue) as $mVal) {

      if (!$bFirst) $aResult[] = ' ';
      $bFirst = false;

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
      $iOffset = 0;

      foreach ($aMatches as $i => $aResult) {

        $iStart = $aResult[0][1];

        $iVarLength = strlen($aResult[0][0]);
        $val = $this->getTemplate()->applyPath($aResult[1][0], '');

        $sStart = substr($sValue, $iOffset, $iStart - $iOffset);

        if ($i == count($aResult) - 1) {

          $mResult[] = array($sStart, $val, substr($sValue, $iStart + $iVarLength));
        }
        else {

          $mResult[] = array($sStart, $val);
          $iOffset += $iStart + $iVarLength;
        }
      }
    }
    else {

      $mResult = $sValue;
    }

    return $mResult;
  }

  public function build(dom\element $el = null) {

    if (!$this->bBuilded) {

      $this->setNode($el, false, false);
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

  protected function parseText(dom\text $node, $bTrim = false) {

    return preg_replace('!\s+!', ' ', parent::parseText($node, $bTrim));
  }

  protected function loadName(dom\element $el) {

    //$sName = ($el->getPrefix() ? $el->getPrefix() . ':' : '') . $el->getName();
    //$sName = $this->getPrefix($el->getNamespace()) . $el->getName();
    $sName = $el->getName();

    return $sName;
  }

  protected function extractTokens(array $aSource) {

    $aContent = $aBefore = $aAttributes = array();

    foreach ($aSource as $val) {

      if (is_array($val)) {

        list($aChildContent, $aChildBefore, $aChildAttributes) = $this->extractTokens($val);

        $aContent = array_merge($aContent, $aChildContent);
        $aBefore = array_merge($aBefore, $aChildBefore);
        $aAttributes = array_merge($aAttributes, $aChildAttributes);
      }
      else if ($val instanceof Token) {

        $sName = $val->getName();

        if (!isset($aAttributes[$sName])) {

          $aAttributes[$sName] = $this->getAttribute($sName);
        }

        $val->setElement($this);
        $aBefore[] = $val->getCall();
      }
      else if ($val instanceof common\structure) {

        list($aChildContent, $aChildBefore, $aChildAttributes) = $this->extractTokens($val->getContent());
/*
        $window = $this->getWindow();

        $test = $window->createVariable('', 'php-boolean');
        $assign = $window->createAssign($test, true);
        $val->addContent($assign);

        $inside->setMain($test);
 */

        $aAttributes = array_merge($aAttributes, $aChildAttributes);

        if ($aChildBefore) {

          if ($aChildContent) {

            $inside = clone $val;

            $inside->setContent($aChildContent);
            $aContent[] = $inside;
          }

          $val->setContent($aChildBefore);
          $aBefore[] = $val;
        }
        else {

          $val->setContent($aChildContent);
          $aContent[] = $val;
        }
      }
      else {

//if ($val instanceof common\basic\Assign) dsp($val->getValue());
        $aContent[] = $val;
      }
    }

    return array($aContent, $aBefore, $aAttributes);
  }

  protected function importTokens(array $aToken) {

    foreach ($aToken as $token) {

      $this->importToken($token);
    }

    return $aToken;
  }

  protected function importToken(Token $token) {

    $token->setElement($this);
  }

  protected function complexAsArray(dom\element $el) {

    $aResult = $aContent = array();

    $aChildren = $this->build();

    $this->start();

    foreach ($aChildren as $child) {

      if (is_string($child)) {

        $child = htmlspecialchars($child);
      }

      $aContent[] = $this->getWindow()->parseArrayables(array($child));
    }

    list($aContent, $aBefore, $aAttributes) = $this->extractTokens($aContent);

    foreach ($aAttributes as $attr) {

      $aResult[] = $attr->getVar()->getInsert();
    }

    $aResult[] = $aBefore;

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

  public function readAttribute($sName, $bDebug = true) {

    if (isset($this->aAttributes[$sName]) && is_array($this->aAttributes[$sName])) {

      $result = $this->aAttributes[$sName];
    }
    else {

      if ($bDebug) $this->launchException ("No static argument named '$sName'");
      $result = null;
    }

    return $result;
  }

  protected function getAttribute($sName, $bLoad = true) {

    if (isset($this->aAttributes[$sName])) {

      $mVal = $this->aAttributes[$sName];

      if (is_array($mVal)) {

        $result = $bLoad ? $this->loadAttribute($sName, $mVal) : null;
      }
      else {

        $result = $mVal;
      }
    }
    else {

      $result = $bLoad ? $this->loadAttribute($sName) : null;
    }

    return $result;
  }

  public function setAttributeComponent(ElementAttribute $component) {

    $this->aAttributes[$component->getName()] = $component;
  }

  protected function addTokenStatic($sName, $sValue) {

    if (isset($this->aDefaultAttributes[$sName])) {

      $this->aDefaultAttributes[$sName][] = $sValue;
      $this->aAttributes[$sName][] = $sValue;
    }
    else {

      $this->setDefaultAttribute($sName, $sValue);
      $this->setAttribute($sName, $sValue);
    }
//dsp('static');
//dsp($this->aAttributes);
  }

  protected function loadAttribute($sName, $mValue = null) {

    $attr = $this->getTemplate()->loadSimpleComponent('element-attribute');
    $attr->init($sName, $mValue);

    $this->setAttributeComponent($attr);

    return $attr;
  }

  public function addToken($sName, $mVal) {

    if ($attr = $this->getAttribute($sName, false)) {

      $result = $attr->addToken($mVal);
    }
    else {

      if (is_string($mVal)) { // static

        $this->addTokenStatic($sName, $mVal);
        $result = null;
      }
      else {

        $result = $this->getAttribute($sName)->addToken($mVal);
      }
    }

    return $result;
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

    $aContent = $this->asArray();
    $result = $this->getParser()->addToResult($aContent, false);

    return $result ? $this->getWindow()->transformContent($result)->asArgument() : null;
  }

  public function asToken() {

    return $this->getNode(false) ? $this->getNode()->asToken() : '[No node defined]';
  }
}


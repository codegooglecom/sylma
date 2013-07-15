<?php

namespace sylma\template\parser\component\element;
use sylma\core, sylma\dom, sylma\parser\languages\common, sylma\template;

class Tokened extends Attributed implements common\arrayable, common\argumentable, template\element, core\tokenable {

  protected function extractTokens(array $aSource) {

    $aContent = $aBefore = array();

    foreach ($aSource as $val) {

      if (is_array($val)) {

        list($aChildContent, $aChildBefore) = $this->extractTokens($val);

        $aContent = array_merge($aContent, $aChildContent);
        $aBefore = array_merge($aBefore, $aChildBefore);
      }
      else if ($val instanceof template\parser\component\Token) {

        $val->setElement($this);
        $aBefore[] = $val->getCall();
      }
      else if ($val instanceof common\structure) {

        if ($val->isExtracted()) {

          $aContent[] = $val;
        }
        else {

          list($aChildContent, $aChildBefore) = $this->extractTokensStructure($val);

          $aContent = array_merge($aContent, $aChildContent);
          $aBefore = array_merge($aBefore, $aChildBefore);
        }
      }
      else {

        $aContent[] = $val;
      }
    }

    return array($aContent, $aBefore);
  }

  protected function extractTokensStructure(common\structure $val) {

    $aContent = $aBefore = $aChildContent = $aChildBefore = array();
    $bExtract = $bContent = false;

    $val->isExtracted(true);

    foreach ($val->getContents() as $sName => $aStructure) {

      list($aChildContent[$sName], $aChildBefore[$sName]) = $this->extractTokens($aStructure);

      if ($aChildContent[$sName]) $bContent = true;
      if ($aChildBefore[$sName]) $bExtract = true;
    }

    if ($bExtract) {

      if ($bContent) {

        $inside = clone $val;

        $inside->setContents($aChildContent);
        $aContent[] = $inside;
      }

      $val->setContents($aChildBefore);
      $aBefore[] = $val;
    }
    else {

      //$val->setContents($aChildContent);
      $aContent[] = $val;
    }

    return array($aContent, $aBefore);
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

  protected function complexAsArray(dom\element $el) {

    $aResult = $aContent = array();

    $aChildren = $this->build();

    $this->start();

    foreach ($aChildren as $child) {

      $aContent[] = $this->getWindow()->parseArrayables(array($child));
    }

    list($aContent, $aBefore) = $this->extractTokens($aContent);

    foreach ($this->getAttributes() as $attr) {

      if ($attr instanceof Attribute) $aResult[] = $attr->getVar()->getInsert();
      //else $aResult[] = $attr;
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
}


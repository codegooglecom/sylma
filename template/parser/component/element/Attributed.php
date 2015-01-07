<?php

namespace sylma\template\parser\component\element;
use sylma\core;

abstract class Attributed extends Domed {

  protected $aDefaultAttributes = array();
  protected $aAttributes = array();

  protected $aAvoidNamespaces = array(
    'http://2013.sylma.org/parser/reflector/builder',
    'http://2013.sylma.org/parser/security',
  );

  protected function buildContent() {

    $el = $this->getNode();

    foreach ($el->getAttributes() as $attr) {

      if (!in_array($attr->getNamespace(), $this->aAvoidNamespaces)) {

        $sName = $this->buildName($attr->getName(), $attr->getNamespace());
        $this->setDefaultAttribute($sName, $attr->getValue());
      }
    }

    $this->resetAttributes();

    parent::buildContent();
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

    $aResult = $aContent = array();
    $bFirst = true;

    $aResult[] = $sName;
    $aResult[] = '="';

    foreach ($this->getWindow()->parseArrayables($aValue) as $mVal) {

      if (!$bFirst) $aContent[] = ' ';
      $bFirst = false;

      if (is_string($mVal)) $aContent[] = $this->parseAttributeValue($mVal);
      else $aContent[] = $mVal;
    }

    $aResult[] = $this->getParser()->xmlize($aContent);

    $aResult[] = '"';

    return $aResult;
  }

  protected function parseAttributeValue($sValue) {

    return $this->getTemplate()->parseValue($sValue);
  }

  public function setAttributes(array $aAttrs) {

    $this->aAttributes = $aAttrs;
  }

  public function setDefaultAttribute($sName, $mVal) {

    if (!is_array($mVal)) $mVal = array($mVal);

    $this->aDefaultAttributes[$sName] = $mVal;
  }

  protected function resetAttributes($aAttributes = array()) {

    $this->aAttributes = $aAttributes ? $aAttributes : $this->aDefaultAttributes;
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

  public function getAttribute($sName, $bLoad = true) {

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

  public function setAttributeComponent(Attribute $component) {

    $this->aAttributes[$component->getName()] = $component;
  }

  protected function loadAttribute($sName, $mValue = null) {

    $attr = $this->getTemplate()->loadSimpleComponent('element-attribute');
    $attr->init($sName, $mValue);

    $this->setAttributeComponent($attr);

    return $attr;
  }
}


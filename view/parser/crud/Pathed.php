<?php

namespace sylma\view\parser\crud;
use sylma\core, sylma\dom, sylma\parser\reflector;

abstract class Pathed extends reflector\component\Foreigner implements reflector\component {

  const DEFAULT_FILE = 'default';

  protected $sAlias = '';

  public function setParser(reflector\domed $parent) {

    parent::setParser($parent);
  }

  protected function loadName() {

    $this->setName($this->readx('@name'));
  }

  public function getAlias() {

    return $this->getName() ? $this->getName() : self::DEFAULT_FILE;
  }

  protected function setName($sValue) {

    $this->sName = $sValue;
  }

  public function isDisabled() {

    return $this->readx('@disable');
  }

  public function getName() {

    return $this->sName;
  }

  protected function extractGroups() {

    $aResult = array();

    if ($sGroups = $this->readx('@groups')) {

      $aResult = explode(',', $sGroups);
    }

    return $aResult;
  }

  protected function loadGroups() {

    $aResult = array();

    foreach ($this->extractGroups() as $sGroup) {

      $sGroup = trim($sGroup);
      $aResult[] = $this->getParser()->getGroup($sGroup);
    }

    return $aResult ? $aResult : null;
  }
}


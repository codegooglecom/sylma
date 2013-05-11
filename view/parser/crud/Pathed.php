<?php

namespace sylma\view\parser\crud;
use sylma\core, sylma\dom, sylma\parser\reflector;

abstract class Pathed extends reflector\component\Foreigner {

  const DEFAULT_FILE = 'default';

  protected $sAlias = '';

  protected function loadName() {

    $this->setName($this->readx('@name'));
  }

  public function getAlias() {

    return $this->getName() ? $this->getName() : self::DEFAULT_FILE;
  }

  protected function setName($sValue) {

    $this->sName = $sValue;
  }

  public function getName() {

    return $this->sName;
  }

  protected function loadGroups() {

    $aResult = array();

    if ($sGroups = $this->readx('@groups')) {

      foreach (explode(',', $sGroups) as $sGroup) {

        $sGroup = trim($sGroup);
        $aResult[] = $this->getParser()->getGroup($sGroup);
      }
    }

    return $aResult ? $aResult : null;
  }

  public function merge($path) {

    return null;
  }
}

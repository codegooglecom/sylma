<?php

namespace sylma\view\parser\crud;
use sylma\core, sylma\dom, sylma\parser\reflector;

class Basic extends reflector\component\Foreigner {

  protected $sAlias = '';

  protected function loadAlias() {

    $this->setAlias($this->readx('@name'));
  }

  public function setAlias($sValue) {

    $this->sAlias = $sValue;
  }

  public function getAlias() {

    return $this->sAlias;
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
}


<?php

namespace sylma\schema\parser\component;
use sylma\core, sylma\dom, sylma\schema;

class Type extends Basic implements core\tokenable {

  protected $define; // restriction, extension

  protected function lookupBase(schema\parser\type $type) {

    $bResult = false;

    foreach ($this->getBases() as $base) {

      if ($base === $type) {

        $bResult = true;
        break;
      }
    }

    return $bResult;
  }

  public function doExtends(schema\parser\type $type) {

    if ($type === $this) {

      $bResult = true;
    }
    else {

      $bResult = $this->lookupBase($type);
    }

    return $bResult;
  }

  protected function setBase(schema\parser\type $type) {

    $this->base = $type;
  }

  protected function setDefine(Restriction $define) {

    $define->setType($this);
    $this->define = $define;
  }

  public function getDefine() {

    return $this->define;
  }

  public function getBases() {

    $base = $this->getBase();
    return $base ? array($base) + $base->getBases() : array();
  }

  protected function getBase() {

    return $this->getDefine() ? $this->getDefine()->getBase() : null;
  }

  public function asToken() {

    return "schema:type [{$this->getNamespace()}:{$this->getName()}]";
  }
}


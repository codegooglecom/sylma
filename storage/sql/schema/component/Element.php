<?php

namespace sylma\storage\sql\schema\component;
use sylma\core, sylma\schema, sylma\parser\languages\common;

class Element extends schema\xsd\component\Element implements common\stringable {

  //abstract protected function useAlias();

  protected $bAlias = false;

  public function getAlias() {

    if ($this->useAlias()) {

      $sResult = $this->getParent()->getName() . '_' . $this->getName();
    }
    else {

      $sResult = $this->getName();
    }

    return $sResult;
  }

  public function useAlias($bVal = null) {

    if (is_bool($bVal)) $this->bAlias = $bVal;

    return $this->bAlias;
  }

  public function asAlias() {

    return $this->asString() . ($this->useAlias() ? ' AS `' . $this->getAlias() . '`' : '');
  }

  public function asString() {

    return $this->getParent()->asString() . '.`' . $this->getName() . "`";
  }

  protected function loadOptional() {

    $sDefault = $this->readx('@default');
    $this->isOptional(!is_null($sDefault) && $sDefault !== '');
  }

  public function getDefault() {

    return $this->readx('@default');
  }
}


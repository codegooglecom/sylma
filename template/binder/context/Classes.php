<?php

namespace sylma\template\binder\context;
use sylma\core;

class Classes extends core\argument\Readable implements core\stringable {

  const PARENT_PATH = 'sylma.binder.classes';
  protected $aKeys = array();

  public function add($mValue, $bRef = false) {

    $sResult = null;
    list($sKey, $sValue) = $mValue;

    if (!in_array($sKey, $this->aKeys)) {

      $this->aKeys[] = $sKey;
      $sResult = parent::add($sValue, $bRef);
    }

    return $sResult;
  }

  public function asString($sTarget = '') {

    if (!$sTarget) $sTarget = self::PARENT_PATH;

    return $sTarget . ' = ' . '{' . implode(',', $this->query()) . '}';
  }

  public function asStringVar() {

    return $this->asString('var classes');
  }
}


<?php

namespace sylma\template\binder\context;
use sylma\core;

class Classes extends core\argument\Readable implements core\stringable {

  const PARENT_PATH = 'sylma.binder.classes';

  public function asString($sTarget = '') {

    if (!$sTarget) $sTarget = self::PARENT_PATH;

    return $sTarget . ' = ' . '{' . implode(',', $this->query()) . '}';
  }

  public function asStringVar() {

    return $this->asString('var classes');
  }
}


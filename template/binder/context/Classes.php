<?php

namespace sylma\template\binder\context;
use sylma\core, sylma\dom, sylma\modules;

class Classes extends modules\html\context\JS implements core\stringable {

  const PARENT_PATH = 'sylma.binder.classes';

  public function asString($sTarget = '') {

    if (!$sTarget) $sTarget = self::PARENT_PATH;

    return $sTarget . ' = ' . '{' . implode(',', $this->query()) . '}';
  }

  public function asStringVar() {

    return $this->asString('var classes');
  }
}


<?php

namespace sylma\parser\js\binder;
use sylma\core, sylma\modules;

class Context extends modules\html\context\JS implements core\stringable {

  const PARENT_PATH = 'sylma.binder.classes';

  public function asString() {

    return self::PARENT_PATH . ' = {' . implode(',', $this->asArray()) . '}';
  }
}


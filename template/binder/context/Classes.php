<?php

namespace sylma\template\binder\context;
use sylma\core, sylma\dom, sylma\modules;

class Classes extends modules\html\context\JS implements core\stringable {

  const PARENT_PATH = 'sylma.binder.classes';

  public function asString() {

    return self::PARENT_PATH . ' = {' . implode(',', $this->query()) . '}';
  }
}


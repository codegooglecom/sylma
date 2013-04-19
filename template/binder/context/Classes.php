<?php

namespace sylma\template\binder\context;
use sylma\core, sylma\dom, sylma\modules;

class Classes extends modules\html\context\JS {

  const PARENT_PATH = 'sylma.binder.classes';

  public function asDOM() {

    if ($this->getArguments()->query()) {

      $this->setArguments(array(self::PARENT_PATH . ' = {' . implode(',', $this->asArray()) . '}'), false);
    }

    return parent::asDOM();
  }
}


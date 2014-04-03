<?php

namespace sylma\action\component;
use sylma\core;

class Named extends Basic {

  protected function loadName() {

    if (!$result = $this->readx('@name')) {

      if ($el = $this->getx('action:name')) {

        $result = $this->getWindow()->toString($this->parseComponentRoot($el));
      }
    }

    return $result;
  }
}


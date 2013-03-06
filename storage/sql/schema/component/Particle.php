<?php

namespace sylma\storage\sql\schema\component;
use sylma\core, sylma\schema, sylma\template;

class Particle extends schema\parser\component\Particle {

  public function getElement($sName) {

    $result = null;

    if ($el = $this->getNode()->getx("sql:*[@name='$sName']")) {

      $result = $this->getParser()->parseComponent($el);
    }

    return $result;
  }
}


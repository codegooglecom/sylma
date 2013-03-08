<?php

namespace sylma\storage\sql\schema\component;
use sylma\core, sylma\schema, sylma\template;

class Particle extends schema\parser\component\Particle {

  public function getElement($sName, $sNamespace) {

    $result = null;

    if (!$result = parent::getElement($sName, $sNamespace)) {

      if ($el = $this->getNode()->getx("sql:*[@name='$sName']")) {

        $result = $this->getParser()->parseComponent($el);
        $result->loadNamespace($sNamespace);
      }
    }

    return $result;
  }
}


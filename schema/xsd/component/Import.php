<?php

namespace sylma\schema\xsd\component;
use sylma\core, sylma\dom, sylma\parser\reflector, sylma\storage\fs;

class Import extends reflector\component\Foreigner {

  public function parseRoot(dom\element $el) {

    $file = $this->getSourceFile($el->read());
    return $this->parseFile($file);
  }

  public function parseFile(fs\file $file) {

    return $this->getParser()->addSchema($file->getDocument());
  }
}


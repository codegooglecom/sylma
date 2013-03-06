<?php

namespace sylma\schema;
use sylma\core, sylma\dom, sylma\parser\reflector, sylma\storage\fs, sylma\parser\languages\common;

class Builder extends reflector\handler\Documented {

  public function getSchema(fs\file $file, common\_window $window = null) {

    $doc = $file->getDocument(array(), \Sylma::MODE_EXECUTE);

    $result = $this->buildReflector($doc, $file, $window);
    $result->parseRoot($doc->getRoot());

    return $result;
  }

  protected function parseReflector(reflector\domed $reflector, dom\document $doc) {

    return $reflector;
  }
}

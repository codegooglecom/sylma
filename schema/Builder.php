<?php

namespace sylma\schema;
use sylma\core, sylma\dom, sylma\parser\reflector, sylma\storage\fs, sylma\parser\languages\common;

class Builder extends reflector\builder\Documented {

  public function setWindow(common\_window $window) {

    return parent::setWindow($window);
  }

  public function setFile(fs\file $file) {

    return parent::setFile($file);
  }

  public function setDocument(dom\handler $doc) {

    return $this->document = $doc;
  }

  public function getSchema() {

    $file = $this->getFile('', false);

    if ($file) {

      $doc = $this->getFile()->getDocument(array(), \Sylma::MODE_EXECUTE);
    }
    else {

      $doc = $this->getDocument();
    }

    $result = $this->reflectMain($doc, $file, $this->getWindow(false));
    $result->parseRoot($doc->getRoot());

    return $result;
  }

  protected function parseReflector(reflector\domed $reflector, dom\document $doc) {

    return $reflector;
  }
}

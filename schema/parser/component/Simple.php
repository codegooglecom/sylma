<?php

namespace sylma\schema\parser\component;
use sylma\core, sylma\schema;

class Simple extends Basic implements schema\parser\type {

  public function isComplex() {

    return false;
  }

  public function isSimple() {

    return true;
  }

  public function validate($sValue) {


  }

  public function getNamespace($sPrefix = '') {

    return parent::getNamespace($sPrefix);
  }
}


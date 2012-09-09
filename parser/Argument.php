<?php

namespace sylma\parser;
use sylma\core, sylma\parser\languages\php;

\Sylma::load('/core/argument/Domed.php');

class Argument extends core\argument\Domed {

  protected static function normalizeObject($val) {

    if ($val instanceof php\basic\instance\_Object) {

      \Sylma::throwException(sprintf('Cannot normalize object instance of %s', $val->getInterface()->getName()));
    }
    else {

      $mResult = parent::normalizeObject($val);
    }

    return $mResult;
  }
}
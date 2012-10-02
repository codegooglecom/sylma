<?php

namespace sylma\parser\languages\php;
use sylma\core, sylma\parser\languages\php;

\Sylma::load('/core/argument/Domed.php');

class Argument extends core\argument\Domed {

  protected static function normalizeObject($val, $bEmpty = false) {

    if ($val instanceof php\basic\instance\_Object) {

      \Sylma::throwException(sprintf('Cannot normalize object instance of %s', $val->getInterface()->getName()));
    }
    else {

      $mResult = parent::normalizeObject($val, $bEmpty);
    }

    return $mResult;
  }


}
<?php

namespace sylma\parser\languages\php;
use sylma\core, sylma\parser\languages\php, sylma\parser\languages\common;

class Argument extends core\argument\Setable {

  protected static function normalizeObject($val, $bEmpty = false) {

    if ($val instanceof common\_object && !$val instanceof common\argumentable) {

      \Sylma::throwException(sprintf('Cannot normalize object instance of %s', $val->getInterface()->getName()));
    }
    else {

      $mResult = parent::normalizeObject($val, $bEmpty);
    }

    return $mResult;
  }


}
<?php

namespace sylma\parser\languages\php;
use sylma\core, sylma\parser\languages\php, sylma\parser\languages\common;

class Argument extends core\argument\Setable {

  protected $window;

  public function setWindow(common\_window $window) {

    $this->window = $window;
  }

  protected function getWindow() {

    return $this->window;
  }

  protected function normalizeObject($val, $bEmpty = false) {

    if ($val instanceof common\_object && !$val instanceof common\argumentable) {

      \Sylma::throwException(sprintf('Cannot normalize object instance of %s', $val->getInterface()->getName()));
    }
    else if ($val instanceof common\argumentable) {

      $mResult = $this->normalizeArgument($val->asArgument());
    }
    else if ($val instanceof common\stringable) {

      $mResult = $this->normalizeArgument($this->getWindow()->argToInstance($val->asString())->asArgument());
    }
    else if ($val instanceof common\arrayable) {

      $mResult = $this->getWindow()->arrayToString($val->asArray());
    }
    else {

      $mResult = parent::normalizeObject($val, $bEmpty);
    }

    return $mResult;
  }


}
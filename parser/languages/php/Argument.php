<?php

namespace sylma\parser\languages\php;
use sylma\core, sylma\parser\languages\php, sylma\parser\languages\common;

class Argument extends core\argument\Readable {

  protected $window;

  public function setWindow(common\_window $window) {

    $this->window = $window;
  }

  protected function getWindow() {

    if (!$this->window) {

      $this->launchException('No window defined');
    }

    return $this->window;
  }

  protected function normalizeObject($val, $bEmpty = false) {

    if ($val instanceof common\_object && !$val instanceof common\argumentable) {

      \Sylma::throwException(sprintf('Cannot normalize object instance of %s', $val->getInterface()->getName()));
    }
    else if ($val instanceof common\argumentable) {

      if ($arg = $val->asArgument()) {

        $mResult = $arg; //$this->normalizeArgument($arg);
      }
      else {

        $mResult = null;
      }
    }
    else if ($val instanceof common\stringable) {

      if ($sValue = $val->asString()) {

        $mResult = $this->normalizeArgument($this->getWindow()->argToInstance($sValue)->asArgument());
      }
    }
    else if ($val instanceof common\arrayable) {

      $mResult = $this->getWindow()->toString($val->asArray());
    }
    else {

      $mResult = parent::normalizeObject($val, $bEmpty);
    }

    return $mResult;
  }


}
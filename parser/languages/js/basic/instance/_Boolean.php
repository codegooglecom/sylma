<?php

namespace sylma\parser\languages\js\basic\instance;
use sylma\parser\languages\js, sylma\parser\languages\common;

class _Boolean extends _Object {

  protected $bValue = null;

  public function __construct(common\_window $window, $mValue) {

    $this->setControler($window);
    $this->setInterface('Boolean');

    $this->setValue($mValue);
  }

  protected function setValue($mValue) {

    if (is_bool($mValue)) {

      $this->bValue =  $mValue;
    }
    else if (is_string($mValue)) {

      if ($mValue == 'true') $this->bValue = true;
      else if ($mValue != 'false') {

        $this->getControler()->throwException(sprintf('Unknown value for boolean conversion : %s', $mValue));
      }
    }
    else {

      $this->bValue =  (bool) $mValue;
    }
  }

  protected function getValue($bString = false) {

    if ($bString) return $this->bValue ? 'true' : 'false';
    else return $this->bValue;
  }

  public function asArgument() {

    return $this->getControler()->createArgument(array(
      'boolean' => array(
        '@value' => $this->getValue(true),
      )));
  }
}

<?php

namespace sylma\parser\action\php\basic\instance;
use sylma\parser\action\php;

require_once(dirname(__dir__) . '/_Scalar.php');
require_once(dirname(dirname(__dir__)) . '/_instance.php');

class _Boolean extends php\basic\_Scalar implements php\_instance {

  protected $sFormat = 'php-boolean';
  protected $bValue = null;

  public function __construct(php\_window $controler, $mValue) {

    $this->setControler($controler);
    $this->setValue($mValue);
  }

  protected function setValue($mValue) {

    $bValue = false;

    if (is_string($mValue)) {

      if ($mValue == 'true') $bValue = true;
      else if ($mValue != 'false') {

        $this->throwException(txt('Unknown value for boolean conversion : %s', $mValue));
      }
    }
    else if (is_numeric($mValue)) {

      $bValue = (bool) $mValue;
    }

    $this->bValue = $bValue;
  }

  public function useFormat($sFormat) {

    return parent::useFormat($sFormat) || $sFormat == 'php-bool';
  }

  protected function getValue($bString = false) {

    if ($bString) return $this->bValue ? 'true' : 'false';
    else return $this->bValue;
  }

  public function asArgument() {

    return $this->getControler()->createArgument(array('boolean' => $this->getValue(true)));
  }
}

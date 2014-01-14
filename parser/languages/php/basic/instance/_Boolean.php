<?php

namespace sylma\parser\languages\php\basic\instance;
use sylma\parser\languages\php, sylma\parser\languages\common;

require_once('_Scalar.php');
require_once('parser/languages/common/_instance.php');

class _Boolean extends _Scalar implements common\_instance {

  protected $sFormat = 'php-boolean';

  protected $bValue = null;
  protected $content;

  public function __construct(common\_window $controler, $mValue) {

    $this->setControler($controler);
    $this->setValue($mValue);
  }

  protected function setValue($mValue) {

    $bValue = false;

    if (is_bool($mValue)) {

      $this->bValue =  $mValue;
    }
    else if (is_string($mValue)) {

      if ($mValue == 'true') $this->bValue = true;
      else if ($mValue != 'false') {

        $this->getControler()->throwException(sprintf('Unknown value for boolean conversion : %s', $mValue));
      }
    }
    else if (is_numeric($mValue)) {

      $this->bValue =  (bool) $mValue;
    }
    else {

      $this->content = $mValue;
    }
  }

  public function useFormat($sFormat) {

    return parent::useFormat($sFormat) || $sFormat == 'php-bool';
  }

  protected function getValue($bString = false) {

    if ($bString) return $this->bValue ? 'true' : 'false';
    else return $this->bValue;
  }

  public function asArgument() {

    if ($this->content) {

      $mContent = $this->content;
    }
    else {

      $mContent = array('@value' => $this->getValue(true));
    }

    return $this->getControler()->createArgument(array('boolean' => $mContent));
  }
}

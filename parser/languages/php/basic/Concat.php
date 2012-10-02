<?php

namespace sylma\parser\languages\php\basic;
use sylma\parser\languages\common, sylma\parser\languages\php, sylma\core;

require_once('instance/_Scalar.php');
require_once('core/argumentable.php');
require_once('parser/languages/common/_instance.php');

class Concat extends instance\_Scalar implements common\_instance, core\argumentable {

  protected $sFormat = 'php-string';
  protected $aValues = array();

  public function __construct(common\_window $controler, $mValue) {

    $this->setControler($controler);
    $this->setValues($mValue);
    //$this->loadValues();
  }

  protected function setValues($mValue) {

    if (is_array($mValue)) {

      foreach ($mValue as $mVal) {

        $this->setValues($mVal);
      }
    }
    else {

      $this->aValues[] = $mValue;
    }
  }

  protected function loadValues() {

    $aResult = array();

    foreach ($this->aValues as $mValue) {

      $aResult[] = $this->getControler()->convertToString($mValue);
    }

    return $aResult;
  }

  public function asArgument() {

    if (!$aValues = $this->aValues) {

      $this->getControler()->throwException('No value defined for string');
    }

    if (count($aValues) === 1) {

      $aResult = array('cast' => array(
        '@type' => 'string',
        array_pop($aValues),
      ));
    }
    else {

      $aResult = array('concat' => $aValues);
    }

    return $this->getControler()->createArgument($aResult);
  }
}
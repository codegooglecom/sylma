<?php

namespace sylma\parser\action\php\basic;
use sylma\parser\action\php, sylma\core;

require_once('instance/_Scalar.php');
require_once('core/argumentable.php');
require_once(dirname(__dir__) . '/_instance.php');

class Concat extends instance\_Scalar implements php\_instance, core\argumentable {

  protected $sFormat = 'php-string';

  public function __construct(php\_window $controler, $mValue) {

    $this->setControler($controler);
    $this->setValue($mValue);
  }

  protected function setValue($mValue) {

    if (is_array($mValue)) {

      foreach ($mValue as $mVal) {

        $this->setValue($mVal);
      }
    }
    else {

      $this->aValues[] = $this->getControler()->convertToString($mValue);
    }
  }

  public function asArgument() {

    if (!$this->aValues) {

      $this->getControler()->throwException(t('No value defined for string'));
    }

    if (count($this->aValues) === 1) {

      $aResult = array('cast' => array(
        '@type' => 'string',
        array_pop($this->aValues),
      ));
    }
    else {

      $aResult = array('concat' => $this->aValues);
    }

    return $this->getControler()->createArgument($aResult);
  }
}
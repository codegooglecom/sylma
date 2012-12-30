<?php

namespace sylma\parser\languages\php\basic\instance;
use sylma\core, sylma\parser\languages\common, sylma\parser\languages\php;

require_once('_Scalar.php');
require_once('parser/languages/common/_instance.php');

class _String extends _Scalar implements common\_instance {

  private $mValue = '';
  protected $sFormat = 'php-string';

  public function __construct(common\_window $controler, $mValue = '') {

    $this->mValue = $mValue;
    $this->setControler($controler);
  }

  public function getValue() {

    return $this->mValue;
  }

  public function setValue($mValue) {

    if (is_string($mValue)) {

      $mResult = $mValue;
    }
    else if (is_object($mValue) && $mValue instanceof core\stringable) {

      $mResult = $mValue;
    }
    else {

      $this->getControler()->throwException(sprintf('Cannot insert object @class ', get_class($mValue)));
    }

    $this->mValue = $mResult;
  }

  public function asArgument() {

    $mVal = $this->mValue;

    $sValue =
      $mVal instanceof core\stringable ?
        $mVal->asString() :
        $mVal;

    return $this->getControler()->createArgument(array(
      'string' => str_replace('\'', '\\\'', $sValue),
    ));
  }
}
<?php

namespace sylma\parser\languages\php\basic\instance;
use sylma\core, sylma\parser\languages\common, sylma\parser\languages\php;

class _String extends _Scalar implements common\_instance, core\tokenable, common\arrayable {

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

  protected function formatString($sValue) {

    return str_replace('\'', '\\\'', $sValue);
  }

  public function asArray() {

    return array($this->getValue());
  }

  public function asArgument() {

    $mVal = $this->mValue;
    $mVal = $mVal instanceof core\stringable ? $mVal->asString() : $mVal;

    return $this->getControler()->createArgument(array(
      'string' => is_string($mVal) ? $this->formatString($mVal) : $mVal,
    ));
  }

  public function asToken() {

    return is_string($this->getValue()) ? ' [' . $this->getValue() . ']' : 'var value';
  }
}
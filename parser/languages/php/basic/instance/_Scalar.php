<?php

namespace sylma\parser\languages\php\basic\instance;
use sylma\core, sylma\parser\languages\php, sylma\parser\languages\common;

abstract class _Scalar extends common\basic\Controled implements common\_scalar {

  protected $sFormat;

  protected function setFormat($sFormat) {

    $this->sFormat = $sFormat;
  }

  public function useFormat($sFormat) {

    return $this->sFormat == $sFormat;
  }
}

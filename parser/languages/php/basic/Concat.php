<?php

namespace sylma\parser\languages\php\basic;
use sylma\parser\languages\common, sylma\parser\languages\php, sylma\core;

class Concat extends common\basic\Concat implements common\_instance, common\_scalar {

  protected $sFormat = 'php-string';

  public function useFormat($sFormat) {

    return $sFormat === $this->sFormat;
  }
}
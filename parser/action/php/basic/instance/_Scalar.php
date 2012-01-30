<?php

namespace sylma\parser\action\php\basic\instance;
use sylma\core, sylma\parser\action\php;

require_once('core/module/Argumented.php');

require_once(dirname(dirname(__dir__)) . '/_scalar.php');
require_once('core/argumentable.php');

abstract class _Scalar extends core\module\Argumented implements php\_scalar, core\argumentable {

  protected $sFormat;

  protected function setFormat($sFormat) {

    $this->sFormat = $sFormat;
  }

  public function useFormat($sFormat) {

    return $this->sFormat = $sFormat;
  }
}

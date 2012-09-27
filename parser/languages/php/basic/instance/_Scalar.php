<?php

namespace sylma\parser\languages\php\basic\instance;
use sylma\core, sylma\parser\languages\php, sylma\parser\languages\common;

require_once('core/module/Argumented.php');

require_once('parser/languages/common/_scalar.php');
require_once('core/argumentable.php');
\Sylma::load('../../../common/basic/Controled.php', __DIR__);

abstract class _Scalar extends common\basic\Controled implements common\_scalar, core\argumentable {

  protected $sFormat;

  protected function setFormat($sFormat) {

    $this->sFormat = $sFormat;
  }

  public function useFormat($sFormat) {

    return $this->sFormat == $sFormat;
  }
}

<?php

namespace sylma\parser\languages\php\basic;
use \sylma\core, \sylma\parser\languages\common, \sylma\parser\languages\php;

require_once('_Var.php');
require_once('parser/languages/common/_scalar.php');

class _ScalarVar extends _Var implements common\_scalar {

  public function useFormat($sFormat) {

    return $this->getInstance()->useFormat($sFormat);
  }

  protected function setInstance(common\_instance $instance) {

    if (!$instance instanceof common\_scalar) {

      $this->getControler()->throwException(sprintf('Bad instance for scalar var : %s', get_class($instance)));
    }

    parent::setInstance($instance);
  }
}
<?php

namespace sylma\parser\languages\php\basic\instance;
use \sylma\parser\languages\common, \sylma\parser\languages\php, sylma\core;

class _Class extends _Object implements common\_object, common\scope, common\_instance, common\argumentable {

  public function asArgument() {

    return $this->getControler()->createArgument(array('class' => $this->getInterface()->getName()));
  }
}
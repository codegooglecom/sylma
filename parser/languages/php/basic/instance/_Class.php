<?php

namespace sylma\parser\languages\php\basic\instance;
use \sylma\parser\languages\common, \sylma\parser\languages\php, sylma\core;

class _Class extends _Object implements common\_object, common\scope, common\_instance, common\argumentable, common\callable {

  public function call($sMethod, array $aArguments = array(), $mReturn = null, $bVar = false) {

    $call = $this->getControler()->createCall($this, $sMethod, $mReturn, $aArguments);

    return $bVar ? $call->getVar() : $call;
  }

  public function asArgument() {

    return $this->getControler()->createArgument(array('class' => $this->getInterface()->getName()));
  }
}
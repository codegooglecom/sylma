<?php

namespace sylma\view\test\grouped\samples;
use sylma\core, sylma\schema\cached\form;

class Dummy02 extends core\module\Domed {

  public function read($sPath, $bDebug = true) {

    return $sPath === 'abcdef' ? 'hello world' : '';
  }

  public function sum() {

    return $this->sum;
  }
}


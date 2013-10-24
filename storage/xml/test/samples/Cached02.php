<?php

namespace sylma\storage\xml\test\samples;
use sylma\core;

class Cached02 extends core\module\Domed {

  public function parse($sResult) {

    return $this->createDocument('<div class="result">' . $sResult . '</div>');
  }
}


<?php

namespace sylma\template\parser\component;
use sylma\core, sylma\dom, sylma\parser\reflector, sylma\parser\languages\common, sylma\template\parser as parser_ns;

class Stringed extends reflector\component\Foreigner {

  protected function toString($mContent) {

    return $this->getParser()->toString($mContent);
  }

  protected function addToResult($mContent, $bAdd = true) {

    return $this->getParser()->addToResult($mContent, $bAdd);
  }
}


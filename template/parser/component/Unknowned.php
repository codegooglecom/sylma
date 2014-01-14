<?php

namespace sylma\template\parser\component;
use sylma\core, sylma\dom, sylma\parser\reflector, sylma\parser\languages\common, sylma\template\parser as parser_ns;

class Unknowned extends Child {

  protected $allowUnknown = true;
  protected $allowForeign = true;

  protected function loadElementUnknown(dom\element $el) {

    return $this->getTemplate()->loadElement($el);
  }
}


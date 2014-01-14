<?php

namespace sylma\template\parser\component;
use sylma\core, sylma\dom, sylma\parser\reflector, sylma\parser\languages\common, sylma\template as template_ns;

class _Else extends Unknowned implements template_ns\parser\component {

  protected $reflector;

  public function parseRoot(dom\element $el) {

    $this->setNode($el);
    //$this->allowForeign(true);
    //$this->allowUnknown(true);
    $this->allowText(true);
  }

  public function parseContent() {

    return $this->parseChildren($this->getNode()->getChildren());
  }
}


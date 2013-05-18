<?php

namespace sylma\template\parser\component;
use sylma\core, sylma\dom, sylma\template\parser, sylma\parser\languages\common;

class Read extends Child implements common\arrayable, parser\component {

  public function parseRoot(dom\element $el) {

    $this->setNode($el);
  }

  public function build() {

    $result = $this->getTemplate()->applyRead();

    $this->log("Read");
    $aResult = $this->getWindow()->parseArrayables(array($result));

    return $aResult;
  }

  public function asArray() {

    return $this->build();
  }
}


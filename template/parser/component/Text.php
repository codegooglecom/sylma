<?php

namespace sylma\template\parser\component;
use sylma\core, sylma\dom, sylma\parser\languages\common, sylma\template\parser;

class Text extends Child implements common\arrayable, parser\component {

  public function parseRoot(dom\element $el) {

    $this->setNode($el);

    $this->allowText(true);
  }

  protected function build() {
/*
    if ($this->getNode()->isComplex()) {

      $this->launchException('Text component must not be complex');
    }
*/
    return $this->getNode()->countChildren() ? $this->parseComponentRoot($this->getNode()) : '';
  }

  public function asArray() {

    return array($this->build());
  }
}


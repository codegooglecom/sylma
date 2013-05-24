<?php

namespace sylma\template\parser\component;
use sylma\core, sylma\dom, sylma\parser\reflector, sylma\parser\languages\common, sylma\template as template_ns;

class _If extends Unknowned implements common\arrayable, template_ns\parser\component {

  public function parseRoot(dom\element $el) {

    $this->setNode($el);
    //$this->allowForeign(true);
    //$this->allowUnknown(true);
    $this->allowText(true);
  }

  public function asArray() {

    $aChildren = $this->parseChildren($this->getNode()->getChildren());

    $if = $this->getTemplate()->getPather()->parseExpression($this->readx('@test'));

    $if->setContent($aChildren);

    return array($if);
  }
}


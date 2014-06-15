<?php

namespace sylma\template\parser\component;
use sylma\core, sylma\dom, sylma\parser\languages\common, sylma\template\parser;

class Format extends Child implements common\arrayable, parser\component {

  public function parseRoot(dom\element $el) {

    $this->setNode($el, true);
  }

  public function build() {

    $aArguments = $this->getHandler()->getPather()->parseArguments($this->readx('@arguments'));
    $content = $this->parseComponentRoot($this->getNode());

    $reflector = $this->createDummy($this->readx('@type'), $aArguments, null, false, true);
    $result = $reflector->call('format', array($content, $aArguments));

    return $result;
  }

  public function asArray() {

    return array($this->build());
  }
}


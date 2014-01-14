<?php

namespace sylma\template\parser\component;
use sylma\core, sylma\dom, sylma\parser\languages\common, sylma\template\parser;

class CData extends Child implements common\arrayable, parser\component {

  protected $var;

  public function parseRoot(dom\element $el) {

    $this->allowForeign(true);
    $this->allowText(true);

    $this->setNode($el);
  }

  public function build() {

    return $this->parseComponentRoot($this->getNode());
  }

  public function asArray() {

    return array('<![CDATA[', $this->build(), ']]>');
  }
}


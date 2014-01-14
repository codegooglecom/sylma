<?php

namespace sylma\template\parser\component;
use sylma\core, sylma\dom, sylma\parser\languages\common, sylma\template\parser;

class Apply extends Child implements common\arrayable, parser\component {

  public function parseRoot(dom\element $el) {

    //parent::parseRoot($el);
  }

  public function asArray() {

    return array();
  }
}


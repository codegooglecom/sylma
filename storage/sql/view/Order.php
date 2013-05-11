<?php

namespace sylma\storage\sql\view;
use sylma\core, sylma\dom, sylma\parser\reflector, sylma\parser\languages\common;

class Order extends reflector\component\Foreigner implements reflector\component, common\arrayable {

  public function parseRoot(dom\element $el) {

    $this->setNode($el);
  }

  protected function build() {

  }

  public function asArray() {

    return array();
  }
}


<?php

namespace sylma\view\parser\crud;
use sylma\core, sylma\dom, sylma\parser\languages\common, sylma\template\parser;

class Path extends parser\component\Child implements parser\component, common\arrayable {

  public function parseRoot(dom\element $el) {

    //$this->setNode($el);
  }

  public function asArray() {

    return array($this->getRoot()->getView()->asPath());
  }
}


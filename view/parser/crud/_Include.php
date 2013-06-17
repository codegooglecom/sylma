<?php

namespace sylma\view\parser\crud;
use sylma\core, sylma\dom, sylma\parser\languages\common, sylma\template, sylma\view\parser;

class _Include extends template\parser\component\Child implements template\parser\component, common\arrayable {

  public function parseRoot(dom\element $el) {

    $this->setNode($el);
  }

  protected function build(parser\builder\Router $root) {

    $path = $root->getPath($this->readx('@path', true));
/*
    if ($path == $this->getRoot()->getView()) {

      $this->launchException('Recursive call detected', get_defined_vars());
    }
*/
    return $root->callScript($root->getPathFile($path), $this->getWindow(), '\sylma\dom\handler', false);
  }

  public function asArray() {

    return array($this->build($this->getRoot()));
  }
}


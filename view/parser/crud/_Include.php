<?php

namespace sylma\view\parser\crud;
use sylma\core, sylma\dom, sylma\parser\languages\common, sylma\template\parser;

class _Include extends parser\component\Child implements parser\component, common\arrayable {

  public function parseRoot(dom\element $el) {

    $this->setNode($el);
  }

  public function asArray() {

    $root = $this->getRoot();
    $path = $root->getPath($this->readx('@path', true));
/*
    if ($path == $this->getRoot()->getView()) {

      $this->launchException('Recursive call detected', get_defined_vars());
    }
*/
    $result = $root->callScript($root->getPathFile($path), $this->getWindow(), '\sylma\dom\handler', false);

    return array($result);
  }
}


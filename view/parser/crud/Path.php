<?php

namespace sylma\view\parser\crud;
use sylma\core, sylma\dom, sylma\parser\languages\common, sylma\template, sylma\view\parser\builder;

class Path extends template\parser\component\Child implements template\parser\component, common\arrayable {

  public function parseRoot(dom\element $el) {

    if ($el->countChildren()) {

      $this->launchException('No content allowed');
    }

    $this->setNode($el);
  }

  protected function build(builder\Router $root) {

    if ($sPath = $this->readx('@path', false)) {

      $path = $root->getPath($sPath);
      $sResult = $path->asPath();
    }
    else {

      $sResult = $root->asPath();
    }

    return $sResult;
  }

  public function asArray() {

    return array($this->build($this->getRoot()));
  }
}


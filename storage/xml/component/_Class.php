<?php

namespace sylma\storage\xml\component;
use sylma\core, sylma\dom, sylma\parser\reflector, sylma\parser\languages\common, sylma\core\functions\path;

class _Class extends reflector\component\Foreigner implements reflector\component, common\arrayable {

  public function parseRoot(dom\element $el) {

    $this->setNode($el);

    \Sylma::load('/core/functions/Path.php');

    $this->getParser()->setReflector(path\toAbsolute($this->readx(), str_replace('/', '\\', (string) $this->getSourceDirectory()), '\\'));
  }

  public function asArray() {

    return array();
  }

}


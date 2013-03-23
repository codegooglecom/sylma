<?php

namespace sylma\view\parser\crud;
use sylma\core, sylma\dom;

class Route extends Routed {

  public function parseRoot(dom\element $el) {

    if ($el->countChildren() != 2) {

      $this->launchException('Route should contains exactly 2 children');
    }

    $this->setNode($el);

    $this->main = $this->loadView($this->getx('view:view[not(@name)]', true));
    $this->sub = $this->loadView($this->getx('view:view[@name]', true));
  }

  protected function loadView(dom\element $el) {

    $result = $this->loadComponent('component/' . $el->getName(), $el);
    $result->parseRoot($el, $this);

    return $result;
  }

  public function getMain() {

    return $this->main;
  }

  public function getSub() {

    return $this->sub;
  }
}


<?php

namespace sylma\view\parser\crud;
use sylma\core, sylma\dom;

class Route extends Basic implements dom\domable {

  protected $local;

  public function parseRoot(dom\element $el) {

    $this->setNode($el);

    $this->main = $this->loadView($this->getx('view:view[not(@name)]', true));
    $this->sub = $this->loadView($this->getx('view:view[@name]', true));

    if ($local = $this->getx('self:local')) {

      $this->local = $this->parseComponent($local);
    }

    $this->loadAlias();
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

  public function asDOM() {

    $aResult = array();

    if ($aGroups = $this->loadGroups()) $aResult[] = $aGroups;
    if ($this->local) $aResult[] = $this->local->asDOM();

    return $aResult ? $aResult : null;
  }
}


<?php

namespace sylma\view\parser\crud;
use sylma\core, sylma\dom;

class View extends Basic {

  protected $route;

  public function parseRoot(dom\element $el, Route $parent = null) {

    $this->setNode($el);
    if ($parent) $this->setRoute($parent);
    $this->loadAlias();
  }

  public function getRoute() {

    return $this->route;
  }

  public function setRoute(Route $route) {

    $this->route = $route;
  }

  public function getName() {

    $sPrefix = $this->getRoute() ? $this->getRoute()->getAlias() : '';
    $sSuffix = parent::getAlias() ? '_' . parent::getAlias() : '';

    return $sPrefix . $sSuffix;
  }

  public function asDocument() {

    //$this->getNode()->add($this->getParser()->getResource());
    $this->getNode()->shift($this->loadGroups());
    $this->getNode()->shift($this->getRoute());
    $this->getNode()->shift($this->getParser()->getGlobal());

    return $this->getNode()->getHandler();
  }
}


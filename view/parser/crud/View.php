<?php

namespace sylma\view\parser\crud;
use sylma\core, sylma\dom;

class View extends Routed {

  protected $route;

  public function parseRoot(dom\element $el, Route $parent = null) {

    $this->setNode($el);
    if ($parent) $this->setRoute($parent);
  }

  public function getRoute() {

    return $this->route;
  }

  public function setRoute(Route $route) {

    $this->route = $route;
  }

  public function getAlias() {

    $sPrefix = $this->getRoute() ? $this->getRoute()->getAlias() : '';
    $sSuffix = parent::getAlias() ? '_' . parent::getAlias() : '';

    return $sPrefix . $sSuffix;
  }

  public function getMode() {

    return $this->readx('@mode');
  }

  public function asDocument() {

    //$this->getNode()->add($this->getParser()->getResource());
    $this->getNode()->add($this->getParser()->getGlobal());

    return $this->getNode()->getDocument();
  }
}


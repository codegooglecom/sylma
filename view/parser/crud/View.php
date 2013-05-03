<?php

namespace sylma\view\parser\crud;
use sylma\core, sylma\dom;

class View extends Path {

  protected $route;

  public function parseRoot(dom\element $el, Route $parent = null) {

    $this->setNode($el);
    if ($parent) $this->setRoute($parent);
    $this->loadName();
  }

  public function getRoute() {

    return $this->route;
  }

  public function setRoute(Route $route) {

    $this->route = $route;
  }

  public function getAlias() {

    $sPrefix = $this->getRoute() ? $this->getRoute()->getAlias() : '';
    $sContent = $this->getName() || $sPrefix ? $this->getName() : self::DEFAULT_FILE;

    return $sPrefix . ($sPrefix && $sContent ? '_' : '') . $sContent;
  }

  public function merge($path) {

    $this->getNode()->shift($path->asDocument()->queryx('* | @*'));
  }

  public function asDocument() {

    //$this->getNode()->add($this->getParser()->getResource());
    $this->getNode()->shift($this->loadGroups());
    $this->getNode()->shift($this->getRoute());
    $this->getNode()->shift($this->getParser()->getGlobal());

    return $this->getNode()->getHandler();
  }
}


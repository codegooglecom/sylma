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

    $root = $this->getParser()->getRoot();

    return $this->getAlias() ? $this->getAlias() : $root::MODE_DEFAULT;
  }

  public function getAlias($bSimple = false) {

    if ($bSimple) {

      $sResult = parent::getAlias();
    }
    else {

      $sPrefix = $this->getRoute() ? $this->getRoute()->getAlias() : '';
      $sSuffix = parent::getAlias() ? '_' . parent::getAlias() : '';

      $sResult = $sPrefix . $sSuffix;
    }

    return $sResult;
  }

  public function asDocument() {

    //$this->getNode()->add($this->getParser()->getResource());
    $this->getNode()->add($this->getParser()->getGlobal());
    $this->getNode()->add($this->getRoute());

    return $this->getNode()->getHandler();
  }
}


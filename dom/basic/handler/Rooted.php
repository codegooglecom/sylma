<?php

namespace sylma\dom\basic\handler;
use sylma\dom;

/**
 * Extends main dom\element root's methods
 */
class Rooted extends Documented implements dom\handler {

  public function getx($sQuery, array $aNS = array(), $bDebug = true) {

    $result = $this->getRoot()->getx($sQuery, $aNS, $bDebug);

    return $result;
  }

  public function readx($sQuery = '', array $aNS = array(), $bDebug = true) {

    $sResult = $this->getRoot()->readx($sQuery, $aNS, $bDebug);

    return $sResult;
  }

  public function queryx($sQuery = '', array $aNS = array(), $bDebug = true, $bConvert = true) {

    return $this->getRoot()->queryx($sQuery, $aNS, $bDebug, $bConvert);
  }

  public function add() {

    return $this->getRoot()->add(func_get_args());
  }

  public function shift() {

    return $this->getRoot()->shift(func_get_args());
  }

  public function getChildren() {

    return $this->getRoot()->getChildren(func_get_args());
  }

  public function hasChildren() {

    return $this->getRoot()->hasChildren();
  }

  public function countChildren() {

    return $this->getRoot()->countChildren();
  }

  public function getFirst() {

    return $this->getRoot()->getFirst();
  }

  public function getLast() {

    return $this->getRoot()->getLast();
  }

  public function remove() {

    return $this->getRoot()->remove();
  }
}
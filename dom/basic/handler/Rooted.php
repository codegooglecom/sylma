<?php

namespace sylma\dom\basic\handler;
use \sylma\dom;

require_once('Documented.php');

/**
 * Extends main dom\element root's methods
 */
class Rooted extends Documented {

  protected function checkRoot() {

    if (!$root = $this->getRoot()) {

      $this->throwException(t('No root element defined'));
    }

    return $root;
  }

  public function getx($sQuery, array $aNS = array(), $bDebug = true) {

    $root = $this->checkRoot();
    $result = $root->getx($sQuery, $aNS, $bDebug);

    return $result;
  }

  public function readx($sQuery = '', array $aNS = array(), $bDebug = true) {

    $root = $this->checkRoot();
    $sResult = $root->readx($sQuery, $aNS, $bDebug);

    return $sResult;
  }

  public function queryx($sQuery = '', array $aNS = array(), $bDebug = true, $bConvert = true) {

    $root = $this->checkRoot();
    return $root->queryx($sQuery, $aNS, $bDebug, $bConvert);
  }

  public function add() {

    $root = $this->checkRoot();
    return $root->add(func_get_args());
  }

  public function getChildren() {

    $root = $this->checkRoot();
    return $root->getChildren(func_get_args());
  }

  public function hasChildren() {

    $root = $this->checkRoot();
    return $root->hasChildren();
  }

  public function countChildren() {

    $root = $this->checkRoot();
    return $root->countChildren();
  }

  public function getFirst() {

    $root = $this->checkRoot();
    return $root->getFirst();
  }

  public function getLast() {

    $root = $this->checkRoot();
    return $root->getLast();
  }
}
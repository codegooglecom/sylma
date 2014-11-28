<?php

namespace sylma\storage\sql\template\component;
use sylma\core, sylma\template;

class Dummed extends Rooted {

  public function reflectApplyFunction($sName, array $aPath, $sMode, $bRead = false, $sArguments = '', array $aArguments = array()) {

    switch ($sName) {

      case 'dummy' :

        $result = $this->reflectDummy($aPath, $aArguments, $sMode, $bRead);
        break;

      case 'source' :

        $result = $this->reflectSource($aPath, $aArguments, $sMode);
        break;

      default :

        $result = $this->getHandler()->getView()->getCurrentTemplate()->reflectApplyFunction($sName, $sArguments);
    }

    return $result;
  }

  protected function getDummy() {

    if (!$this->dummy) {

      $result = $this->getWindow()->createVar($this->buildReflector(array(), 'dummy'));
      $this->aStart[] = $result->getInsert();

      $this->dummy = $result;
    }

    return $this->dummy;
  }

  protected function reflectDummy(array $aPath, array $aArguments = array(), $sMode = '', $bRead) {

    return $this->getHandler()->parsePathToken($this->getTree(), $aPath, $sMode, $bRead, $aArguments);
  }

  protected function reflectSource(array $aPath, array $aArguments = array(), $sMode = '') {

    $result = null;

    if (!$this->getTree()) {

      $window = $this->getWindow();
      $tree = $this->create('tree', array($this->getHandler(), $this->getFactory()->findClass('tree')));

      $result = $tree->loadDummy();

      //$this->getTable()->setSource($tree->getDummy());
      $this->setTree($tree);
    }

    return $result;
  }

  protected function setTree(template\parser\tree $tree) {

    $this->tree = $tree;
  }

  /**
   * @usedby sql\template\component\Table::startStatic()
   * @return xml\tree
   */
  public function getTree() {

    return $this->tree;
  }

}



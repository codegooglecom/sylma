<?php

namespace sylma\storage\sql\template\component;
use sylma\core, sylma\template, sylma\parser\languages\common;

class Dummed extends Rooted {

  protected $dummy;
  protected $tree;

  public function reflectApplyFunction($sName, array $aPath, $sMode, $bRead = false, $sArguments = '', array $aArguments = array()) {

    switch ($sName) {

      case 'dummy' :

        $result = $this->reflectDummy($aPath, $aArguments, $sMode, $bRead);
        break;

      case 'source' :

        $result = $this->reflectSource($aPath, $aArguments, $sMode);
        break;

      default :

        $result = $this->getHandler()->getCurrentTemplate()->reflectApplyFunction($sName, $aPath, $sMode, $bRead, $sArguments, $aArguments);
    }

    return $result;
  }

  /**
   * @usedby Reference::reflectFunctionRef()
   */
  public function setDummy(common\_var $handler) {

    $this->dummy = $handler;
  }

  /**
   * @usedby Foreign::buildMultiple()
   * @return common\_var|null
   */
  public function getDummy($bDebug = true) {

    if (!$this->dummy) {

      if ($dummy = $this->loadDummy()) {

        $this->setDummy($dummy);
      }
      else if ($bDebug) {

        $this->launchException('No dummy defined');
      }
    }

    return $this->dummy;
  }

  protected function loadDummy() {

    $this->launchException('Must be overrided');
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



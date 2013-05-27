<?php

namespace sylma\storage\sql\template\component;
use sylma\core, sylma\storage\sql;

class Collection extends Rooted implements sql\template\pathable {

  protected $table;
  protected $pager;
  protected $counter;

  public function getElement($sName, $sNamespace) {

    return $this->getTable()->getElement($sName, $sNamespace);
  }

  public function setTable(Table $table) {

    $this->table = $table;
    $this->loadNamespace($table->getNamespace());
  }

  protected function getTable() {

    return $this->table;
  }

  protected function preBuild() {

    $window = $this->getWindow();

    $var = $window->createVariable('item', '\sylma\core\argument', false);
    $this->setSource($var);

    $this->getQuery()->isMultiple(true);
    $this->getTable()->insertQuery(false);
  }

  protected function postBuild($result) {

    $window = $this->getWindow();

    $loop = $window->createLoop($this->getQuery()->getVar(), $this->getSource());
    $loop->setContent($window->parseArrayables(array($result)));

    $aResult[] = $loop;

    return $aResult;
  }

  public function reflectApplyDefault($sPath, array $aPath, $sMode) {

    $this->launchException('No default value defined');
  }

  public function getType($bDebug = true) {

    return null;
  }

  public function reflectApply($sMode = '', $bStatic = false) {

    if ($result = $this->lookupTemplate($sMode)) {

      $result->setTree($this);
      $this->preBuild();
    }
    else {

      if (!$sMode) {

        $this->launchException('Cannot apply collection without template', get_defined_vars());
      }

      $result = null;
    }

    $aResult = array();

    if (!$sMode) {

      $aResult[] = $this->getCounter();
    }

    $aResult[] = $this->getWindow()->parseArrayables(array($result));

    return $aResult;
  }

  public function reflectApplyAll($sMode) {

    $aResult = $this->preBuild();

    $this->getTable()->setSource($this->getSource());

    $content = $this->getTable()->reflectApply($sMode);

    $aResult[] = $this->getQuery();
    $aResult[] = $this->postBuild($content);

    return $aResult;
  }

  public function reflectApplyFunction($sName, array $aPath, $sMode) {

    switch ($sName) {

      case 'static' :

        $result = $this->getTable()->reflectApply($sMode, true);
        break;

      case 'count' :

        $result = $this->getCount();
        break;

      case 'pager' :

        $result = $this->getPager()->reflectApply($sMode);
        break;

      default :

        $this->launchException("Function '$sName' unknown", get_defined_vars());
    }

    return $result;
  }

  public function setPager(sql\template\Pager $pager) {

    $pager->setCollection($this);
    $pager->setParser($this->getParser()->getView());

    $this->pager = $pager;
  }

  protected function getPager() {

    if (!$this->pager) {

      $this->launchException('No pager defined');
    }

    return $this->pager;
  }

  public function setLimit($offset, $count) {

    $query = $this->getQuery();

    $query->setOffset($offset);
    $query->setCount($count);
  }

  public function getCount() {

    return $this->getCounter()->getVar();
  }

  protected function loadCounter() {

    $query = clone $this->getQuery();
    $this->getQuery()->setClone($query);

    $result = $this->loadSimpleComponent('counter');
    $result->setQuery($query);

    return $result;
  }

  protected function getCounter() {

    if (!$this->counter) {

      $this->counter = $this->loadCounter();
    }

    return $this->counter;
  }
}


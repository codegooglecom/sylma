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
  }

  protected function postBuild($result) {

    $window = $this->getWindow();

    $loop = $window->createLoop($this->getQuery()->getVar(), $this->getSource());
    $window->setScope($loop);

    $loop->addContent($this->getParser()->getView()->addToResult($result, false));
    $window->stopScope();

    $result = $loop;

    return $result;
  }

  public function reflectApplyDefault($sPath, array $aPath, $sMode) {

    //dsp($sPath);
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

    return $result;
  }

  public function reflectApplyAll($sMode) {

    $this->preBuild();

    $this->getQuery()->isMultiple(true);
    $this->getTable()->setSource($this->getSource());

    $content = $this->getTable()->reflectApply($sMode);

    return $this->postBuild($content);
  }

  public function reflectApplyFunction($sName, array $aPath, $sMode) {

    switch ($sName) {

      case 'static' :

        $result = $this->getTable()->reflectApply($sMode, true);
        break;

      case 'count' :

        $result = $this->getCounter();
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

  public function getCounter() {

    if (!$this->counter) {

      $query = clone $this->getQuery();
      $this->getQuery()->setClone($query);

      $query->clearColumns();
      $query->clearLimit();

      $query->setColumn('COUNT(*)');
      $query->isMultiple(false);
      $query->setMethod('read');

      $this->counter = $query->getVar();
    }

    return $this->counter;
  }
}


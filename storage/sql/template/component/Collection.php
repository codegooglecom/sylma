<?php

namespace sylma\storage\sql\template\component;
use sylma\core, sylma\storage\sql, sylma\template;

class Collection extends Rooted implements sql\template\pathable {

  protected $table;
  protected $pager;
  protected $counter;
  protected $key;

  protected $dummy;
  protected $tree;

  /**
   * Before loop
   */
  protected $aStart = array();
  protected $bBuilded = false;

  public function getElement($sName, $sNamespace = null) {

    return $this->getTable()->getElement($sName, $sNamespace);
  }

  /**
   * @usedby sql\template\component\Reference::loadCollection()
   */
  public function setTable(Table $table, $bReset = false) {

    $sNamespace = $this->getHandler()->getNamespace('sql');

    $this->setType($this->getHandler()->getType('collection', $sNamespace));
    $this->setNamespace($sNamespace);
    //$this->setNamespace($table->getNamespace(), 'element', false);
    $this->setName('[collection]');
    $this->setQuery($table->getQuery($bReset));

    $this->table = $table;
    $table->setCollection($this);
    //$this->loadNamespace($table->getNamespace());
  }

  public function getTable() {

    return $this->table;
  }

  protected function preBuild() {

    $window = $this->getWindow();

    $source = $window->createVariable('', '\sylma\core\argument', false);
    $key = $window->createVariable('', 'php-string', false);

    $this->setSource($source);
    $this->setKey($key);

    $this->getQuery()->isMultiple(true);
    $this->getTable()->insertQuery(false);
  }

  protected function postBuild($result) {

    $window = $this->getWindow();

    $loop = $window->createLoop($this->getQuery()->getVar(), $this->getSource(), $this->getKey());
    $loop->setContent($window->parseArrayables(array($result)));

    $aResult[] = $loop;

    return $aResult;
  }

  public function reflectRead(array $aPath = array(), array $aArguments = array()) {

    if (!$aPath) {

      $this->launchException('Cannot read collection');
    }

    return $this->getParse()->parsePathToken($this, $aPath, $aArguments, true);
  }

  public function reflectApplyDefault($sPath, array $aPath, $sMode, $bRead = false, array $aArguments = array()) {

    $aResult = $this->prepareApply();
    array_unshift($aPath, $sPath);
    $aResult[] = $this->getHandler()->parsePathToken($this->getTable(), $aPath, $sMode, $bRead, $aArguments);

    return $aResult;
  }

  public function reflectApply($sMode = '', array $aArguments = array()) {

    if ($result = $this->lookupTemplate($sMode)) {

      $result->setTree($this);
      $result->sendArguments($aArguments);

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

      //$aResult[] = $this->getCounter();
    }

    $aResult[] = $result;

    return $aResult;
  }

  protected function prepareApply() {

    $this->preBuild();

    $this->getTable()->setSource($this->getSource());
    $this->getTable()->setKey($this->getKey());
    $this->getTable()->setQuery($this->getQuery());
    //$this->getTable()->setCollection($this);

    if ($this->insertQuery()) {

      $aResult[] = $this->getQuery();
    }

    $aResult[] = $this->buildStart();

    $this->insertQuery(false);

    return $aResult;
  }

  public function reflectApplyAll($sMode, array $aArguments = array()) {

    $aResult = $this->prepareApply();
    $aResult[] = $this->postBuild($this->getTable()->reflectApply($sMode, $aArguments));

    return $aResult;
  }

  /**
   * @usedby self::reflectApplyAll() dummy callback
   * @return array
   */
  public function getStart() {

    return $this->aStart;
  }

  protected function buildStart() {

    $aResult = array();

    if (!$this->bBuilded) {

      $collection = $this;
      $window = $this->getWindow();

      $aResult[] = $window->createCaller(function() use ($collection, $window) {

        return $window->createGroup($collection->getStart());
      });

      $this->bBuilded = true;
    }

    return $aResult;
  }

  protected function getDummy() {

    if (!$this->dummy) {

      $result = $this->getWindow()->createVar($this->buildReflector(array(), 'dummy'));
      $this->aStart[] = $result->getInsert();

      $this->dummy = $result;
    }

    return $this->dummy;
  }

  public function reflectApplyFunction($sName, array $aPath, $sMode, $bRead = false, $sArguments = '', array $aArguments = array()) {

    switch ($sName) {

      case 'init' :

        $result = $this->buildStart();
        break;

      case 'static' :

        $result = $aPath ? $this->getHandler()->parsePathToken($this->getTable(), $aPath, $sMode, true, $aArguments) : $this->getTable()->reflectApply($sMode, $aArguments, true);
        break;

      case 'counter' :

        $result = $this->getCounter();
        break;

      case 'count' :

        $result = $this->getCount();
        break;

      case 'pager' :

        $result = $this->getPager()->reflectApply($sMode);
        break;

      case 'dummy' :

        $result = $this->reflectDummy($aPath, $aArguments, $sMode, $bRead);
        break;

      case 'source' :

        $result = $this->reflectSource($aPath, $aArguments, $sMode);
        break;

      case 'distinct' :

        $aFunctionArguments = $this->getHandler()->getPather()->parseArguments($sArguments, $sMode, $bRead, false);
        $result = $this->getDistinct($aFunctionArguments, $aPath, $sMode, $aArguments);
        break;

      case 'has-children' :

        $this->preBuild();

        $result = array();

        if ($this->insertQuery()) $result[] = $this->getQuery();
        $this->insertQuery(false);

        $result[] = $this->getQuery()->getVar()->call('hasChildren');

        break;

      default :

        $result = $this->getHandler()->getView()->getCurrentTemplate()->reflectApplyFunction($sName, $sArguments);
    }

    return $result;
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

  protected function getDistinct(array $aFunctionArguments, array $aPath, $sMode, array $aArguments = array()) {

    $el = array_pop($aFunctionArguments);
    $table = $this->loadDistinctElement($el);

    $query = clone $this->getQuery();
    $this->getQuery()->addClone($query);

    $query->clearColumns();
    $query->clearLimit();
    $query->clearOrder();
    //$query->clearJoins();

    $query->setMethod('extract');

    $query->setColumn(array('DISTINCT ', $el->asString()));

    $collection = $this->loadSimpleComponent('component/collection');
    $collection->setTable($table);
    $collection->setQuery($table->getQuery());

    $window = $this->getWindow();

    $sIDS = array('(', $window->callFunction('implode', 'php-string', array(',', $query->getVar()->call('asArray'))), ')');

    $collection->getQuery()->setWhere($table->getElement('id'), 'IN', $sIDS);

    $aResult[] = $query;
    $aResult[] = $aPath ? $this->getHandler()->parsePath($collection, implode('/', $aPath), $sMode, $aArguments) : $collection->reflectApply($sMode, $aArguments);

    return $aResult;
  }

  protected function loadDistinctElement(Foreign $el) {

    return $el->getElementRef();
  }

  public function setPager(sql\pager\Tree $pager) {

    $pager->setCollection($this);
    $pager->setParser($this->getHandler()->getView());

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
    $this->getQuery()->addClone($query);

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


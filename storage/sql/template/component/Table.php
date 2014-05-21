<?php

namespace sylma\storage\sql\template\component;
use sylma\core, sylma\dom, sylma\storage\sql, sylma\schema, sylma\template;

class Table extends Rooted implements sql\template\pathable, schema\parser\element {

  const MODE_EMPTY = 'sylma:empty';

  protected $sMode = 'select';

  protected $bBuilded = false;
  protected $aColumns = array();
  protected $bSub = false;
  protected $bStatic = false;
  protected $bMultiple = true;

  protected $loop;
  protected $connection;
  protected $collection;

  public function init() {
    //$this->loadConnection();
  }

  public function setParent(schema\parser\element $parent) {

    $this->parent = $parent;
  }

  public function getParent($bDebug = true) {

    if (!$this->parent && $bDebug) {

      $this->throwException('No parent');
    }

    return $this->parent;
  }

  public function loadConnection($sConnection = '') {

    $aConnection = array();

    if (!$sConnection) {

      $sConnection = $this->getConnectionAlias();
    }

    if ($sConnection) {

      $aConnection[] = $sConnection;
    }

    $result = $this->getWindow()->addControler(self::DB_MANAGER)->call('getConnection', $aConnection);

    $this->connection = $result;
  }

  public function getConnection() {

    if (!$this->connection) {

      $this->loadConnection();
    }

    return $this->connection;
  }

  public function setCollection(Collection $val) {

    $this->collection = $val;
  }

  /**
   * @usedby sql\template\component\Foreign::importElementRef()
   * @return sql\template\component\Collection
   */
  public function getCollection($bDebug = true) {

    if ($bDebug && !$this->collection) {

      $this->launchException('No collection defined');
    }

    return $this->collection;
  }

  protected function getMode() {

    return $this->sMode;
  }

  public function isMultiple($bValue = null) {

    if (is_bool($bValue)) $this->bMultiple = $bValue;

    return $this->bMultiple;
  }

  public function isSub($bVal = null) {

    if (is_bool($bVal)) $this->bSub = $bVal;

    return $this->bSub;
  }

  public function getQuery($bReset = false) {

    if (!$this->query || $bReset) {

      $this->setQuery($this->buildQuery());
    }

    return $this->query;
  }

  protected function buildQuery() {

    $result = $this->createQuery($this->getMode());

    return $result;
  }

  public function getSource($bDebug = true) {

    if (!$bDebug || $this->source) {

      $result = $this->source;
    }
    else {

      if ($this->isStatic()) {

        $this->launchException('Cannot load query in static mode');
      }

      $result = $this->getQuery()->getVar();
    }

    return $result;
  }

  public function getKey() {

    return parent::getKey();
  }

  protected function createQuery($sName) {

    $query = $this->loadSimpleComponent("template/$sName", $this);

    $query->setConnection($this->getConnection());
    $query->setTable($this);
    $query->setCharset($this->getCharset());

    return $query;
  }

  protected function getColumns() {

    return $this->aColumns;
  }

  protected function addColumn(schema\parser\element $el) {

    $this->aColumns[$el->getName()] = $el;
  }

  public function addElementToQuery(schema\parser\element $el) {

    $this->addColumn($el);

    $query = $this->getQuery();
    $query->setElement($el);
  }

  public function reflectApplyDefault($sPath, array $aPath, $sMode, $bRead = false, array $aArguments = array(), $bStatic = false) {

    if ($bStatic) {

      $aStat = $this->startStatic();
    }

    $content = $this->getHandler()->reflectApplyDefault($this, $sPath, $aPath, $sMode, $bRead, $aArguments);
    $result = $this->getWindow()->parse($content, true);

    if ($bStatic) {

      $this->stopStatic($aStat);
    }

    return $result;
  }

  public function isStatic($bValue = null) {

    if (is_bool($bValue)) {

      $this->bStatic = $bValue;
    }

    return $this->bStatic;
  }

  public function reflectApply($sMode = '', array $aArguments = array(), $bStatic = false) {

    if ($tpl = $this->lookupTemplate($sMode)) {

      $tpl->setTree($this);
      $tpl->sendArguments($aArguments);

      if (!$bStatic && $this->insertQuery()) {

        $this->insertQuery(false);

        $query = $this->getQuery();

        if ($this->isOptional()) {

          $query->isOptional($this->isOptional());

          $window = $this->getWindow();
          $view = $this->getParser()->getView();
          $condition = $window->createCondition($query->getVar()->call('query'), $view->addToResult($tpl, false));

          if ($empty = $this->lookupTemplate(self::MODE_EMPTY)) {

            $empty->setTree($this);
            $empty->sendArguments($aArguments);

            $condition->addElse($view->addToResult($empty, false));
          }

          $aResult[] = $query;
          $aResult[] = $condition;
        }
        else {

          $aResult[] = $query;
          $aResult[] = $tpl;
        }
      }
      else {

        $aResult[] = $this->reflectApplyStatic($bStatic, $tpl);
      }
    }
    else {

      if (!$sMode) {

        $this->launchException('Cannot apply table without template and without mode');
      }

      $aResult = array();
    }

    return $aResult;
  }

  protected function reflectApplyStatic($bStatic, template\parser\template $tpl = null) {

    if ($bStatic) {

      $aStat = $this->startStatic();
    }

    $aResult = $this->getWindow()->parse($tpl, true);

    if ($bStatic) {

      $this->stopStatic($aStat);
    }

    return $aResult;
  }

  protected function startStatic() {

    $current = $this->getSource(false);

    if ($collection = $this->getCollection(false) and $tree = $collection->getTree()) {

      $this->setSource($tree->getDummy());
    }

    $bCurrent = $this->isStatic();
    $this->isStatic(true);

    return array(
      'source' => $current,
      'static' => $bCurrent,
    );
  }

  protected function stopStatic(array $aStat) {

    $this->setSource($aStat['source']);
    $this->isStatic($aStat['static']);
  }

  public function reflectRead() {

    $this->launchException('Cannot read table');
  }

  public function reflectApplyFunction($sName, array $aPath, $sMode, $bRead = false, $sArguments = '', array $aArguments = array()) {

    switch ($sName) {

      //case 'apply' : $result = $this->reflectApply(''); break;
      case 'name' : $result = $this->getName(); break;
      case 'title' : $result = $this->getTitle(); break;
      case 'position' :$result = $this->getPosition();  break;
      case 'collection' :

        $result = $this->getHandler()->parsePathToken($this->getCollection(), $aPath, $sMode, $bRead, $aArguments);
        break;

      case 'parent' :

        $result = $this->getHandler()->parsePathToken($this->getParent(), $aPath, $sMode, $bRead, $aArguments);
        break;

      case 'dummy' :

        $result = $this->getCollection()->reflectApplyFunction($sName, $aPath, $sMode, $bRead, $sArguments, $aArguments);
        break;

      default :

        $result = $this->getHandler()->getView()->getCurrentTemplate()->reflectApplyFunction($sName, $sArguments);
    }

    return $result;
  }

  protected function getPosition() {

    if (!$key = $this->getKey()) {

      //$key = $this->getParent()->getKey();
      $this->launchException('No key defined, maybe not in a loop');
    }

    $window = $this->getWindow();

    return $window->createExpression(array(
      $key,
      $window->createOperator('+'),
      $window->createNumeric(1),
    ));
  }

  public function reflectApplyAll($sMode) {

    $aResult = array();

    foreach ($this->getElements() as $element) {

      $aResult[] = $element->reflectApply($sMode);
    }

    return $aResult;
  }

  public function reflectApplyAllExcluding(array $aExcluded, $sMode) {

    $aResult = array();
    $aRemoved = array();

    foreach ($aExcluded as $sName) {

      list($sNamespace, $sName) = $this->parseName($sName);

      if (!$removed = $this->getElement($sName, $sNamespace, false)) {

        $removed = $this->getParser()->getType($sName, $sNamespace, false);
      }

      $aRemoved[] = $removed;
    }

    foreach ($this->getElements() as $element) {

      foreach ($aRemoved as $excluded) {

        if ($excluded === $element || $element->getType() === $excluded) {

          continue 2;
        }
      }

      $aResult[] = $element->reflectApply($sMode);
    }

    return $aResult;
  }

  public function reflectRegister() {

    $this->launchException('Table cannot be registered');
  }
}


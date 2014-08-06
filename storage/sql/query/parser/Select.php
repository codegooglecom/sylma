<?php

namespace sylma\storage\sql\query\parser;
use sylma\core, sylma\parser\languages\common, sylma\schema;

class Select extends Ordered {

  protected $sMethod = '';
  protected $aElements = array();
  protected $aKeyElements = array();

  protected $offset = '0';
  protected $count;

  protected $aClones = array();
  protected $main;
  protected $group;

  public function setElement(schema\parser\element $el, $bDistinct = false) {

    $sName = $el->getName();
    $bAdd = true;

    if (array_key_exists($sName, $this->aKeyElements)) {

      if ($el === $this->aKeyElements[$sName]) {

        $bAdd = false;
      }
      else {

        $el->useAlias(true);
      }
    }
    else {

      $this->aKeyElements[$sName] = $el;
    }

    if ($bAdd) {

      $sName = $el->asAlias();

      if ($bDistinct) {

        $mContent = array('DISTINCT ', $sName);
        $this->setColumn($mContent, true);
      }
      else {

        $mContent = $sName;
        $this->setColumn($mContent);
      }
    }

    $this->aElements[] = $el;
  }

  protected function getElements() {

    return $this->aElements;
  }

  public function setColumn($val, $bShift = false, $sAlias = '') {

    if ($sAlias) {

      $this->aColumns[$sAlias] = $val;
    }
    else {

      parent::setColumn($val, $bShift);
    }
  }

  public function getColumn($sAlias) {

    if (!isset($this->aColumns[$sAlias])) {

      $this->launchException('No alias defined with this name', get_defined_vars());
    }

    return true;
  }

  protected function parseColumns() {

    $aResult = array();

    if (!$this->aColumns) {

      $aResult[] = '*';
    }
    else {

      $aResult = parent::parseColumns();
    }

    return $aResult;
  }

  public function getColumns() {

    return $this->aColumns;
  }

  public function isEmpty() {

    return !$this->aColumns;
  }

  public function clearColumns() {

    $this->aColumns = array();
  }

  public function setWhere($val1, $sOp, $val2, $sLog = 'AND') {

    foreach($this->getClones() as $clone) {

      $clone->setWhere($val1, $sOp, $val2, $sLog);
    }

    return parent::setWhere($val1, $sOp, $val2, $sLog);
  }

  protected function insertDynamicWhere($bVal = null) {

    if (is_bool($bVal)) $this->bDynamicWhere = $bVal;

    return $this->bDynamicWhere;
  }

  protected function buildDynamicWhere() {

    if ($this->getMain()) {

      $aResult = $this->getMain()->buildDynamicWhere();
    }
    else if ($this->getDynamicWhere() && $this->insertDynamicWhere()) {

      $aResult = parent::buildDynamicWhere();
    }
    else {

      $aResult = null;
    }

    $this->insertDynamicWhere(false);

    return $aResult;
  }

  protected function getWheres() {

    if ($this->getMain() && $this->getMain()->getDynamicWhere()) {

      $result = $this->getMain()->getDynamicWhere();
    }
    else {

      $result = parent::getWheres();
    }

    return $result;
  }

  /**
   * @usedby sql\template\component\Collection::getDistinct()
   * @usedby sql\template\component\Counter::setQuery()
   * @usedby sql\template\component\view\Foreign::reflectValues()
   */
  public function setMethod($sMethod) {

    $this->sMethod = $sMethod;
  }

  public function setOffset($offset) {

    $this->offset = $offset;
  }

  protected function getOffset() {

    return $this->offset;
  }

  public function setCount($count) {

    $this->count = $count;
  }

  protected function getCount() {

    return $this->count;
  }

  protected function getLimit() {

    if ($this->getCount()) {

      $aResult = array(' LIMIT ', $this->getOffset(), ', ', $this->getCount());
    }
    else {

      $aResult = array();
    }

    return $aResult;
  }

  public function clearLimit() {

    $this->offset = $this->count = null;
  }

  public function getCall($bDebug = false) {

    $bDebug = $this->isMultiple() || $this->isOptional() ? false: true;

    return parent::getCall($bDebug);
  }

  protected function build() {

    if (!$sMethod = $this->getMethod()) {

      $this->setMethod($this->isMultiple() ? 'query' : 'get');
    }

    parent::build();
  }

  public function setGroup($group) {

    $this->group = $group;

    foreach ($this->getClones() as $clone) {

      $clone->setGroup($group);
    }
  }

  protected function getGroup() {

    return $this->group ? array(' GROUP BY ', $this->group) : null;
  }

  public function getString() {

    $aQuery = array(
      'SELECT ',
      $this->parseColumns(),
      ' FROM ',
      $this->getTables(),
      $this->getJoins(),
      $this->getWheres(),
      $this->getGroup(),
      $this->getOrder(),
      $this->getLimit(),
    );

    return $this->getWindow()->createString($aQuery);
  }

  public function addClone(self $clone) {

    $this->aClones[] = $clone;
    $clone->setMain($this);
  }

  public function setMain(self $main) {

    $this->main = $main;
  }

  protected function getMain() {

    return $this->main;
  }

  protected function getClones() {

    return $this->aClones;
  }

  public function asArgument() {

    $result = null;

    if ($this->getColumns()) {

      $result = $this->getWindow()->createGroup(array(
        $this->buildDynamicWhere(),
        $this->prepareOrder(),
        $this->getVar()->getInsert(),
      ))->asArgument();
    }

    return $result;
  }
}


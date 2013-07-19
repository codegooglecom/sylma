<?php

namespace sylma\storage\sql\query\parser;
use sylma\core, sylma\parser\languages\common, sylma\schema, sylma\storage\sql;

class Select extends Wherer implements common\argumentable {

  protected $sMethod = '';
  protected $aJoins = array();
  protected $aElements = array();
  protected $aKeyElements = array();
  protected $aJoinsElements = array();

  protected $offset = '0';
  protected $count;
  protected $order;

  protected $aClones = array();
  protected $main;

  public function setElement(schema\parser\element $el) {

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

      $this->setColumn($el->asAlias());
    }

    $this->aElements[] = $el;
  }

  protected function getElements() {

    return $this->aElements;
  }

  protected function getColumns() {

    $aResult = array();

    if (!$this->aColumns) {

      $aResult[] = '*';
    }
    else {

      $aResult = parent::getColumns();
    }

    return $aResult;
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

  public function addJoin(sql\schema\table $table, sql\schema\element $field, $val) {

    $bAdd = true;

    foreach ($this->aJoinsElements as $el) {

      if ($el === $field) {

        $bAdd = false;
      }
    }

    if ($bAdd) {

      foreach($this->getClones() as $clone) {

        $clone->addJoin($table, $field, $val);
      }

      $this->aJoins[] = array($table, $field, $val);
      $this->aJoinsElements[] = $field;
    }
  }

  protected function getJoins() {

    $aResult = array();

    foreach ($this->aJoins as $iCurrent => $aJoin) {

      $aResult[] = array(' LEFT JOIN ', $aJoin[0], ' ON ', $aJoin[1], ' = ', $aJoin[2], ' ');
    }

    return $aResult;
  }

  public function clearJoins() {

    $this->aJoins = array();
  }

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

  public function setOrder($val) {

    $this->order = $val;
  }

  protected function getOrder() {

    return $this->order;
  }

  protected function prepareOrder() {

    $aResult = $aJoins = $aElements = array();

    if ($this->getOrder()) {

      // On join, only first element is used as order, maybe todo

      foreach ($this->aJoins as $aJoin) {

        $foreign = $aJoin[2];
        $ref = $aJoin[1];

        if (!$ref instanceof sql\schema\element) {

          $this->launchException('Cannot prepare unknown for order', get_defined_vars());
        }

        foreach ($this->getElements() as $el) {

          if ($el->getParent() === $ref->getParent()) {

            $aElements[$foreign->getName()] = $el;
            break;
          }
        }
      }

      $aResult[] = $this->getOrder()->getInsert();

      if ($aElements) {

        $aResult[] = $this->getOrder()->call('setForeigns', array($aElements));
      }
    }

    return $aResult;
  }

  public function clearOrder() {

    $this->order = null;
  }

  protected function build() {

    if (!$sMethod = $this->getMethod()) {

      $this->setMethod($this->isMultiple() ? 'query' : 'get');
    }

    parent::build();
  }

  public function getString() {

    $aQuery = array('SELECT ', $this->getColumns(), ' FROM ', $this->getTables(), $this->getJoins(), $this->getWheres(), $this->getOrder(), $this->getLimit());

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

    return $this->getWindow()->createGroup(array(
      $this->buildDynamicWhere(),
      $this->prepareOrder(),
      $this->getVar()->getInsert(),
    ))->asArgument();
    //$content = $this->getString();

    //return $content->asArgument();
  }
}


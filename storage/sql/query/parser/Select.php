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
  protected $orderPath;
  protected $orderDynamic;

  protected $aClones = array();
  protected $main;

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

      if ($bDistinct) $mContent = array('DISTINCT ', $sName);
      else $mContent = $sName;

      $this->setColumn($mContent);
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

  public function setOrderPath($sValue) {

    $this->orderPath = $sValue;
  }

  protected function getOrderPath() {

    return $this->orderPath;
  }

  public function setOrderDynamic($content) {

    $this->orderDynamic = $content;
  }

  protected function getOrderDynamic() {

    return $this->orderDynamic;
  }

  protected function getOrder() {

    return $this->order;
  }

  protected function prepareOrder() {

    $aResult = array();
    $obj = null;

    $string = $this->getParser()->getType('string', $this->getParser()->getNamespace('sql'));

    if ($sPath = $this->getOrderPath()) {

      $obj = $this->createObject('order', array($sPath));
      $order = $this->create('order', array($sPath));
      $table = $this->aTables[0];

      $aElements = array();

      foreach ($order->extractPath() as $aElement) {

        $field = $table->getElement($aElement['name']);

        $aElements[$field->getName()] = array(
          'alias' => $field,
          'string' => $field->getType()->doExtends($string),
        );
      }

      $aResult[] = $obj->getInsert();
      $aResult[] = $obj->call('setElements', array($aElements));
    }
    else if ($content = $this->getOrderDynamic()) {

      foreach ($this->getElements() as $field) {

        $aElements[$field->getName()] = array(
          'alias' => $field,
          'string' => $field->getType()->doExtends($string),
        );
      }

      $obj = $this->createObject('order', array($content));

      // On join, only first element is used as order, maybe todo

      foreach ($this->aJoins as $aJoin) {

        $foreign = $aJoin[2];
        $ref = $aJoin[1];

        if (!$ref instanceof sql\schema\element) {

          $this->launchException('Cannot prepare unknown for order', get_defined_vars());
        }

        foreach ($this->getElements() as $el) {

          if ($el->getParent() === $ref->getParent()) {

            $aElements[$foreign->getName()] = array(
              'alias' => $el,
              'string' => $el->getType()->doExtends($string),
            );

            break;
          }
        }
      }

      $aResult[] = $obj->getInsert();
      $aResult[] = $obj->call('setElements', array($aElements));
    }

    $this->order = $obj;

    return $aResult;
  }

  public function getCall($bDebug = false) {

    $bDebug = $this->isMultiple() || $this->isOptional() ? false: true;

    return parent::getCall($bDebug);
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

